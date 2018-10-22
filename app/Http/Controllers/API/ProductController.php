<?php

namespace App\Http\Controllers\API;

use App\Classes\CacheWrapper;
use App\Exceptions\Http;
use App\Http\Controllers\Controller;
use App\Http\Repositories\AttachmentRepository;
use App\Http\Repositories\ProductRepository;
use App\Http\Requests\ProductCreateRequest;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function open()
    {
        $data = "This data is open and can be accessed without the client being authenticated";
        return response()->json(compact('data'), 200);

    }

    /**
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function products($user_id)
    {
        //-- Load products from cache if available
        $user = User::find($user_id);
        $products = ProductRepository::getAll($user);
        $data = $products;

        return response()->json(compact('data'), 200);
    }

    /**
     * @param $user_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($user_id, $id)
    {
        $arrData = ProductRepository::show($id, $user_id);
        $product = $arrData['product'];

        if ($product != null) {
            $arrThumbnails = $arrData['arrThumbnails'];
            $data = [
                'product' => $product,
                'thumbnails' => $arrThumbnails,
            ];

            return response()->json(compact('data'), 200);
        } else {
            return Http::notFound($id, 'product');
        }
    }


    /**
     * @param $user_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function import($user_id, Request $request)
    {
        if ($user_id == Auth::id()) {
            if ($file = $request->file('file')) {
                ProductRepository::import($file);
                $arrImport = session('arrImport');
                $arrErrors = session('arrErrors');
                $data = [
                    'import' => $arrImport,
                    'errors' => $arrErrors,
                ];
                return response()->json(compact('data'), 200);
            }
        } else {
            return Http::notAuthorized($user_id);
        }
    }

    /**
     * @param $user_id
     * @param ProductCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($user_id, ProductCreateRequest $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            $data=$validator->errors();
            return response()->json(compact('data'), 422);
        }

        if ($user_id == Auth::id()) {

            $product = ProductRepository::store($request);
            $result=AttachmentRepository::uploadFile($request,$product->id);
            if($result==='success'){


                //-- Reset product list cache
                CacheWrapper::resetCache(Auth::id(), 'product');
                $data = $product;
                return response()->json(compact('data'), 200);
            }else{
                $product->delete();
                $data=$result;
                return response()->json(compact('data'), 422);
            }


        } else {
            return Http::notAuthorized($user_id);
        }

    }

    private function validator($data)
    {
        return Validator::make($data, [
            'name'=>'required',
            'case_count'=>'required|integer',
            'size'=>'required|integer',
            'brand'=>'required',
            'file'=>'required',
        ]);
    }

}