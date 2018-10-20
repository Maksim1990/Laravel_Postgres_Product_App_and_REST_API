<?php

namespace App\Http\Controllers;

use App\Category;
use App\Config\Config;
use App\Http\Requests\ProductCreateRequest;
use App\Product;
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
        $this->removeDeprecatedAttachments();

        $products = Product::where('user_id', Auth::id())->get();
        return view('products.index', compact('products'));
    }

    public function import($type)
    {

        return view('products.import', compact('type'));
    }

    public function removeDeprecatedAttachments()
    {
        $old_attachments = Attachment::where('product_id', 0)->get();
        if(!empty($old_attachments)){
            foreach ($old_attachments as $attachment){
                if(file_exists(public_path().$attachment->path)){
                    Croppa::delete($attachment->path);
                }
                $attachment->delete();
            }

        }
    }

    public function upload()
    {

        return view('products.upload');
    }

    public function show($id)
    {
        $product=Product::findOrFail($id);
        return view('products.show',compact('product'));
    }

    public function create()
    {
        $this->removeDeprecatedAttachments();
        return view('products.create');
    }

    public function edit($id)
    {

        getVideoThumbnail('ddd');

        $this->removeDeprecatedAttachments();
        $product = Product::where('user_id', Auth::id())->where('id', $id)->first();
        $arrThumbnails=array();
        if(count($product->attachments)>0){
            foreach ($product->attachments as $attachment){
                if($attachment->type=='image'){
                    $arrThumbnails[$attachment->id]=Croppa::url('/uploads/'.$attachment->name, 400, 400, ['resize']);
                }elseif ($attachment->type=='video'){
                    $arrThumbnails[$attachment->id]=getVideoThumbnail($attachment->name);
                }
            }
        }
        dd($arrThumbnails);
        return view('products.edit', compact('product','arrThumbnails'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {


        $product = Product::findOrFail($id);
        $input = $request->all();
        $product->update($input);
        return redirect()->route('index');

    }

    public function store(ProductCreateRequest $request)
    {


        $input = $request->all();
        $user = Auth::user();
        $input['user_id'] = $user->id;
        $input['product_code'] = "FYUFUYFY";


        $product=Product::create($input);

        $attachments = Attachment::where('product_id', 0)->get();
        if(!empty($attachments)){
            foreach ($attachments as $attachment){
                $attachment->update([
                    'product_id'=>$product->id
                ]);
            }

        }

        return redirect()->route('index');


    }


    public function destroy($id)
    {
        $product=Product::findOrFail($id);
        Category::where('product_id',$product->id)->delete();

        if(!empty($product->attachments)){
            foreach ($product->attachments as $attachment){
                if(file_exists(public_path().$attachment->path)){
                    Croppa::delete($attachment->path);
                }
                $attachment->delete();
            }
        }


        Session::flash('product_change','The product has been successfully deleted!');
        $product->delete();
        return redirect()->route('index');
    }

    public function importFile($type, Request $request)
    {

        if ($file = $request->file('file')) {
            if (!($file->getClientSize() > 2100000)) {


                $name = time() . $file->getClientOriginalName();
                //-- Temporarily save uploaded file
                request()->file('file')->storeAs(
                    'public/upload/import/', $name
                );


                if (file_exists(storage_path('/app/public/upload/import/' . $name))) {
                    //unlink(storage_path('/app/public/upload/import/' . $name));
                    $arrImport = (new FastExcel)->import(storage_path('/app/public/upload//import/' . $name), function ($line) {
                        dd($line);
                    });
                }
            }

        }
    }
}
