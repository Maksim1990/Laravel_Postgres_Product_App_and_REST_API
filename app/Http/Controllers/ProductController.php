<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductCreateRequest;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Attachment;

class ProductController extends Controller
{
    public function index()
    {
        $products=Product::where('user_id',Auth::id())->get();
        return view('products.index',compact('products'));
    }

    public function import($type)
    {

        return view('products.import', compact('type'));
    }

    public function upload()
    {

        return view('products.upload');
    }

    public function create()
    {

        return view('products.create');
    }

    public function edit($id)
    {
        $product=Product::where('user_id',Auth::id())->where('id',$id)->first();
        return view('products.edit',compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
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


        Product::create($input);

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
