<?php

namespace App\Http\Controllers;

use App\Category;
use App\Config\Config;
use App\Http\Repositories\ProductRepository;
use App\Http\Requests\ProductCreateRequest;
use App\Interfaces\RedisInterface;
use App\Product;
use App\ProductCategoryPivot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Attachment;
use Croppa;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller implements RedisInterface
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $products = ProductRepository::getAll($user);

        return view('products.index', compact('products'));
    }

    /**
     * @param $id
     * @param $type
     * @return string
     */
    public function resetCache($id, $type)
    {
        //-- Flush cached product's cache for current user
        Cache::tags($type . '_' . $id)->flush();
    }

    /**
     * @param $type
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function import($type)
    {
        return view('products.import', compact('type'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function upload()
    {
        return view('products.upload');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $arrData = ProductRepository::show($id, Auth::id());

        $product = $arrData['product'];
        $arrThumbnails = $arrData['arrThumbnails'];

        //-- Get customized array of categories
        $arrCategories = ProductRepository::buildCategoryLabelsArray($product);

        return view('products.show', compact('product', 'arrThumbnails', 'arrCategories'));
    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $arrThumbnails = array();
        $attachments=Attachment::where('product_id',0)->where('user_id',Auth::id())->get();
        if (count($attachments) > 0) {
            foreach ($attachments as $attachment) {
                if ($attachment->type == 'image') {
                    $arrThumbnails[$attachment->id] = Croppa::url('/uploads/' . $attachment->name, 400, 400, ['resize']);
                } elseif ($attachment->type == 'video') {
                    $arrThumbnails[$attachment->id] = getVideoThumbnail($attachment->name);
                }
            }
        }

        $loadMainJS = true;
        return view('products.create', compact('loadMainJS','attachments','arrThumbnails'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $loadMainJS = true;
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
        //-- Get customized array of categories
        $arrCategories = ProductRepository::buildCategoryLabelsArray($product);
        $strCategories = "";
        if (!empty($arrCategories)) {
            $strCategories = implode(";", $arrCategories);
        }

        return view('products.edit', compact('product', 'arrThumbnails', 'loadMainJS', 'strCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProductCreateRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductCreateRequest $request, $id)
    {
        $product=ProductRepository::update($request,$id);
        return redirect('products/' . $product->id);
    }

    /**
     * @param ProductCreateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProductCreateRequest $request)
    {
        ProductRepository::store($request);

        //-- Reset product list cache
        $this->resetCache(Auth::id(), 'product');

        return redirect()->route('index');
    }



    /**
     * Delete product and relevant content and relations
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $result=ProductRepository::destroy($id);

        if($result==='success'){
            Session::flash('product_change', 'The product has been successfully deleted!');
        }else{
            Session::flash('product_change', $result);
        }

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
                ProductRepository::import($file);
            }
            return redirect()->route('index');
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
