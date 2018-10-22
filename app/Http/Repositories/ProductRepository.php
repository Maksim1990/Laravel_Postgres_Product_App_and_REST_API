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
use App\Product;
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
    static public function getAll(User $user){
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
    static public function buildCategoryLabelsArray($product){
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
     * @param $userId
     * @return array
     */
    static public function show($id, $userId){
        $product = Product::where('id',$id)->where('user_id',$userId)->first();
        $arrThumbnails = array();
        if($product!=null){
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

        $result=[
            'product'=>$product,
            'arrThumbnails'=>$arrThumbnails,
        ];
        return $result;
    }

    static public function import($file){
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


                    Session::flash('arrImport', $arrImport);
                    Session::flash('arrErrors', $arrErrors);


                //-- Remove file after import
                unlink(storage_path('/app/public/upload/import/' . $name));
            }
        }
    }


}