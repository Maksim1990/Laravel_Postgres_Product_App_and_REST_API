<?php

namespace App\Http\Controllers;

use App\Category;
use App\Config\Config;
use App\Http\Requests\ProductCreateRequest;
use App\Product;
use App\ProductCategoryPivot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Attachment;
use Croppa;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('user_id', Auth::id())->get();
        return view('products.index', compact('products'));
    }

    public function import($type)
    {

        return view('products.import', compact('type'));
    }

    public function upload()
    {

        return view('products.upload');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $arrThumbnails = array();
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
        return view('products.show', compact('product', 'arrThumbnails'));
    }

    public function create()
    {

        $loadMainJS=true;
        return view('products.create',compact('loadMainJS'));
    }

    public function edit($id)
    {
        $loadMainJS=true;
        $product = Product::where('user_id', Auth::id())->where('id', $id)->first();
        $arrThumbnails = array();
        if (count($product->attachments) > 0) {
            foreach ($product->attachments as $attachment) {
                if ($attachment->type == 'image') {
                    $arrThumbnails[$attachment->id] = Croppa::url('/uploads/' . $attachment->name, 400, 400, ['resize']);
                } elseif ($attachment->type == 'video') {
                    $arrThumbnails[$attachment->id] = getVideoThumbnail($attachment->name);
                }
            }
        }
        return view('products.edit', compact('product', 'arrThumbnails','loadMainJS'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductCreateRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $input = $request->all();
        $input['barcode'] = generateBarcodeNumber(12);;
        $product->update($input);
        return redirect()->route('index');
    }

    public function store(ProductCreateRequest $request)
    {
        $input = $request->all();
        $linkedCategories = $request->categories_form;
        unset($input['categories_form']);
        $arrCategories=[];
        if(!empty($linkedCategories)){
            $arrCategories=explode(";",$linkedCategories);
        }

        $user = Auth::user();
        $input['user_id'] = $user->id;
        $input['barcode'] = generateBarcodeNumber(12);

        $maxID=Product::where('id','>',0)->orderBy('id','DESC')->limit(1)->first();
        $input['id']=$maxID!=null?$maxID->id+1:1;
        $product = Product::create($input);


        if(!empty($arrCategories)){
            foreach ($arrCategories as $categoryName){
                $categoryData=explode(":",$categoryName);
                foreach ($categoryData as $key=>$cat){
                    $categoryItem = Category::where('name',$cat)->first();
                    if($key>0){
                        $parentCategory=Category::where('name',$categoryData[0])->first();
                    }
                    if($categoryItem==null){
                        $maxID=Category::where('id','>',0)->orderBy('id','DESC')->limit(1)->first();
                        $categoryItem=Category::create([
                            'id'=>$maxID!=null?$maxID->id+1:1,
                            'user_id'=> Auth::id(),
                            'name'=> $cat,
                            'parent'=> $key==0?$key:$parentCategory->id,
                        ]);
                    }

                    if($key==(count($categoryData)-1)){
                        $categoryLink=ProductCategoryPivot::where('product_id',$product->id)->where('category_id',$categoryItem->id)->first();
                        $maxID=ProductCategoryPivot::where('id','>',0)->orderBy('id','DESC')->limit(1)->first();
                        if($categoryLink===null){
                            ProductCategoryPivot::create([
                                'id'=>$maxID!=null?$maxID->id+1:1,
                                'product_id'=>$product->id,
                                'category_id'=>$categoryItem->id,
                            ]);
                        }
                    }
                }

            }
        }


        $attachments = Attachment::where('product_id', 0)->get();
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $attachment->update([
                    'product_id' => $product->id
                ]);
            }

        }

        return redirect()->route('index');


    }


    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        Category::where('product_id', $product->id)->delete();

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
                $attachment->delete();
            }
        }


        Session::flash('product_change', 'The product has been successfully deleted!');
        $product->delete();
        return redirect()->route('index');
    }

    /**
     * Functionality for CSV import
     *
     * @param $type
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function importFile($type, Request $request)
    {

        //-- Check file type
        if ($type === 'csv') {
            if ($file = $request->file('file')) {
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
                                $maxID=Product::where('id','>',0)->orderBy('id','DESC')->limit(1)->first();
                                $product = Product::create([
                                    'id'=>$maxID!=null?$maxID->id+1:1,
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
                                        $stsType = 'image';
                                    }

                                    if (in_array($extension, Config::VIDEO_EXTENSIONS)) {
                                        $blnUploadStatus = true;
                                        $stsType = 'video';
                                    }

                                    if ($blnUploadStatus) {
                                        $maxID=Attachment::where('id','>',0)->orderBy('id','DESC')->limit(1)->first();
                                        Attachment::create([
                                            'id'=>$maxID!=null?$maxID->id+1:1,
                                            'user_id' => Auth::id(),
                                            'product_id' => $product->id,
                                            'name' => $arrData[count($arrData) - 1],
                                            'size' => 0,
                                            'extension' => $extension,
                                            'type' => $stsType,
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

                        Session::flash('arrImport', $arrImport);
                        Session::flash('arrErrors', $arrErrors);
                        //-- Remove file after import
                        unlink(storage_path('/app/public/upload/import/' . $name));

                        return redirect()->route('index');
                    }


                }
            }
        }
    }


    /**
     * Functionality to update caption per each attachment
     * @param Request $request
     */
    public function ajaxUpdateCaption(Request $request)
    {
        $attachment_id = $request->attachment_id;
        $new_caption = $request->new_caption;
        $strError = "";
        $result = "success";

        $attachment = Attachment::find($attachment_id);
        if (!empty($attachment)) {
            $attachment->update([
                'caption' => $new_caption
            ]);

        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $result,
            'error' => $strError
        ));
    }

}
