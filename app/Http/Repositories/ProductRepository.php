<?php
/**
 * Created by PhpStorm.
 * User: Maxim.Narushevich
 * Date: 22.10.2018
 * Time: 12:04
 */

namespace App\Http\Repositories;

use App\Attachment;
use App\Category;
use App\Classes\CacheWrapper;
use App\Config\Config;
use App\Http\Requests\ProductCreateRequest;
use App\Product;
use App\ProductCategoryPivot;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Croppa;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Session;

class ProductRepository
{

    /**
     * @param User $user
     * @return mixed
     */
    static public function getAll(User $user)
    {
        //-- Load products from cache if available
        $products = Cache::tags(['product_' . $user->id])->get('products_list');

        if (!$products) {
            $products = Product::where('user_id', $user->id)->get();
            Cache::tags(['product_' . $user->id])->put('products_list', $products, 22 * 60);
        }
        return $products;
    }

    /**
     * @param $product
     * @return array
     */
    static public function buildCategoryLabelsArray($product)
    {
        $arrCategories = [];
        if (!empty($product->categories)) {
            foreach ($product->categories as $category) {
                if ($category->parent == 0) {
                    $arrCategories[] = $category->name;
                } else {
                    $parentCategory = Category::find($category->parent);
                    $arrCategories[] = $parentCategory->name . ":" . $category->name;
                }
            }
        }

        return $arrCategories;
    }

    /**
     * @param $id
     * @return string
     */
    static public function destroy($id){

        $result='success';
        $product = Product::findOrFail($id);
        $categories = $product->categories;
        if (!empty($categories)) {
            foreach ($categories as $category) {
                ProductCategoryPivot::where('category_id', $category->id)->delete();
                $category->delete();
            }
        }

        if (!empty($product->attachments)) {
            foreach ($product->attachments as $attachment) {
                if (file_exists(public_path() . $attachment->path)) {
                    Croppa::delete($attachment->path);
                }

                //-- Delete video thumbnails
                if (in_array($attachment->extension, Config::VIDEO_EXTENSIONS)) {
                    $thumbnail = getVideoThumbnail($attachment->name);
                    if (file_exists(public_path() . $thumbnail)) {
                        unlink(public_path() . $thumbnail);
                    }
                }

                if(!$attachment->delete()){
                    $result='Some error happened while deleting attachment';
                }
            }
        }
        if(!$product->delete()){
            $result='Some error happened while deleting product';
        }

        //-- Reset category list cache
        CacheWrapper::resetCache(Auth::id(), 'category');

        //-- Reset product list cache
        CacheWrapper::resetCache(Auth::id(), 'product');

        return $result;
    }

    /**
     * @param $id
     * @param $userId
     * @return array
     */
    static public function show($id, $userId)
    {
        $product = Product::where('id', $id)->where('user_id', $userId)->first();
        $arrThumbnails = array();
        if ($product != null) {
            if (count($product->attachments) > 0) {
                foreach ($product->attachments as $attachment) {
                    if ($attachment->import == 'N') {
                        if ($attachment->type == 'image') {
                            $arrThumbnails[$attachment->id] = Croppa::url('/uploads/' . $attachment->name, 400, 400, ['resize']);
                        } elseif ($attachment->type == 'video') {
                            $arrThumbnails[$attachment->id] = getVideoThumbnail($attachment->name);
                        }
                    }
                }
            }
        }

        $result = [
            'product' => $product,
            'arrThumbnails' => $arrThumbnails,
        ];
        return $result;
    }

    /**
     * @param ProductCreateRequest $request
     * @return mixed
     */
    static public function store(ProductCreateRequest $request)
    {
        $input = $request->all();
        $linkedCategories = $request->categories_form;
        unset($input['categories_form']);
        unset($input['file']);
        $arrCategories = [];
        if (!empty($linkedCategories)) {
            $arrCategories = explode(";", $linkedCategories);
        }

        $user = Auth::user();
        $input['user_id'] = $user->id;
        $input['barcode'] = generateBarcodeNumber(12);

        $maxID = Product::where('id', '>', 0)->orderBy('id', 'DESC')->limit(1)->first();
        $input['id'] = $maxID != null ? $maxID->id + 1 : 1;
        $product = Product::create($input);

        if (!empty($arrCategories)) {
            ProductRepository::handleNewCategoryList($product, $arrCategories);
        }

        $attachments = Attachment::where('product_id', 0)->get();
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $attachment->update([
                    'product_id' => $product->id
                ]);
            }
        }

        return $product;
    }


    /**
     * @param $product
     * @param $arrCategories
     */
    static public function handleNewCategoryList($product, $arrCategories)
    {
        foreach ($arrCategories as $categoryName) {
            $categoryData = explode(":", $categoryName);
            foreach ($categoryData as $key => $cat) {
                $categoryItem = Category::where('name', $cat)->first();
                if ($key > 0) {
                    $parentCategory = Category::where('name', $categoryData[0])->first();
                }
                if (!empty($cat)) {

                    if ($categoryItem == null) {
                        $maxID = Category::where('id', '>', 0)->orderBy('id', 'DESC')->limit(1)->first();
                        $categoryItem = Category::create([
                            'id' => $maxID != null ? $maxID->id + 1 : 1,
                            'user_id' => Auth::id(),
                            'name' => $cat,
                            'parent' => $key == 0 ? $key : $parentCategory->id,
                        ]);
                    }

                    if ($key == (count($categoryData) - 1)) {
                        $categoryLink = ProductCategoryPivot::where('product_id', $product->id)->where('category_id', $categoryItem->id)->first();
                        $maxID = ProductCategoryPivot::where('id', '>', 0)->orderBy('id', 'DESC')->limit(1)->first();
                        if ($categoryLink === null) {
                            ProductCategoryPivot::create([
                                'id' => $maxID != null ? $maxID->id + 1 : 1,
                                'product_id' => $product->id,
                                'category_id' => $categoryItem->id,
                            ]);
                        }
                    }
                }
            }
        }

        //-- Reset category list cache
        CacheWrapper::resetCache(Auth::id(), 'category');
    }


    /**
     * @param $file
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    static public function import($file)
    {
        if (!($file->getClientSize() > 2100000)) {


            $name = time() . $file->getClientOriginalName();
            //-- Temporarily save uploaded file
            request()->file('file')->storeAs(
                'public/upload/import/', $name
            );

            $arrImport['intLines'] = 1;
            $arrImport['intImportedLines'] = 0;
            $arrImport['arrResourcesRejected'] = [];
            $arrRequiredFields = ['name', 'barcode', 'brand', 'size', 'case_count'];
            $arrErrors = [];

            if (file_exists(storage_path('/app/public/upload/import/' . $name))) {

                (new FastExcel)->import(storage_path('/app/public/upload//import/' . $name), function ($line) use (&$arrErrors, &$arrImport, $arrRequiredFields) {
                    $arrLine = [];
                    foreach ($line as $key => $val) {
                        $arrKeys = explode(";", $key);
                        $arrValues = explode(";", $val);
                        $arrLine = array_combine($arrKeys, $arrValues);
                    }
                    $blnStatus = true;
                    foreach ($arrRequiredFields as $strField) {
                        if (empty($arrLine[$strField])) {
                            $blnStatus = false;
                            $arrErrors[$arrImport['intLines'] + 1] = " field '" . $strField . "' can't be empty";
                        }
                    }

                    if ($blnStatus && !empty($arrLine['barcode']) && Product::where('barcode', $arrLine['barcode'])->first() !== null) {
                        $blnStatus = false;
                        $arrErrors[$arrImport['intLines'] + 1] = " product with barcode " . $arrLine['barcode'] . " already exist";
                    }

                    if ($blnStatus) {
                        $arrImport['intImportedLines']++;
                        $maxID = Product::where('id', '>', 0)->orderBy('id', 'DESC')->limit(1)->first();
                        $product = Product::create([
                            'id' => $maxID != null ? $maxID->id + 1 : 1,
                            'user_id' => Auth::id(),
                            'name' => $arrLine['name'],
                            'barcode' => $arrLine['barcode'],
                            'description' => $arrLine['description'],
                            'brand' => $arrLine['brand'],
                            'size' => $arrLine['size'],
                            'case_count' => $arrLine['case_count']
                        ]);

                        if (!empty($arrLine['attachment_resource'])) {
                            $arrData = explode("/", $arrLine['attachment_resource']);
                            $extension = substr($arrLine['attachment_resource'], -3);

                            $blnUploadStatus = false;
                            if (in_array($extension, Config::IMAGES_EXTENSIONS)) {
                                $blnUploadStatus = true;
                                $strType = 'image';
                            }

                            if (in_array($extension, Config::VIDEO_EXTENSIONS)) {
                                $blnUploadStatus = true;
                                $strType = 'video';
                            }

                            if ($blnUploadStatus) {
                                $maxID = Attachment::where('id', '>', 0)->orderBy('id', 'DESC')->limit(1)->first();
                                Attachment::create([
                                    'id' => $maxID != null ? $maxID->id + 1 : 1,
                                    'user_id' => Auth::id(),
                                    'product_id' => $product->id,
                                    'name' => $arrData[count($arrData) - 1],
                                    'size' => 0,
                                    'extension' => $extension,
                                    'type' => $strType,
                                    'import' => 'Y',
                                    'path' => $arrLine['attachment_resource']
                                ]);
                            } else {
                                $arrImport['arrResourcesRejected'][] = $arrLine['attachment_resource'];
                            }
                        }

                        return $product;
                    }
                    $arrImport['intLines']++;
                });

                //-- Reset product list cache
                CacheWrapper::resetCache(Auth::id(), 'product');

                //-- Store import result in session
                Session::flash('arrImport', $arrImport);
                Session::flash('arrErrors', $arrErrors);

                //-- Remove file after import
                unlink(storage_path('/app/public/upload/import/' . $name));
            }
        }
    }


}