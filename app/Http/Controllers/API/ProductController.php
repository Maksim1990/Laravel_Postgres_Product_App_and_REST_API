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

    /**
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($user_id)
    {
        if ($user_id == Auth::id()) {
            //-- Load products from cache if available
            $user = User::find($user_id);
            $products = ProductRepository::getAll($user);
            $data = $products;

            return response()->json(compact('data'), 200);
        } else {
            return Http::notAuthorized($user_id);
        }
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($user_id, Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            $data = $validator->errors();
            return response()->json(compact('data'), 422);
        }

        if ($user_id == Auth::id()) {

            $product = ProductRepository::store($request);
            $result = AttachmentRepository::uploadFile($request, $product->id);
            if ($result === 'success') {


                //-- Reset product list cache
                CacheWrapper::resetCache(Auth::id(), 'product');
                $arrThumbnails = ProductRepository::getThumbnails($product);
                $data = [
                    'product' => $product,
                    'thumbnails' => $arrThumbnails,
                ];
                return response()->json(compact('data'), 200);
            } else {
                $product->delete();
                $data = $result;
                return response()->json(compact('data'), 422);
            }
        } else {
            return Http::notAuthorized($user_id);
        }

    }

    /**
     * @param $user_id
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($user_id, Request $request, $id)
    {
//        $validator = $this->validator($request->all(),true);
//        if ($validator->fails()) {
//            $data = $validator->errors();
//            return response()->json(compact('data'), 422);
//        }

        if ($user_id == Auth::id()) {
            $product = ProductRepository::update($request, $id);
            if($request->file('file')){
                $result = AttachmentRepository::uploadFile($request, $product->id);
            }else{
                $result='success';
            }

            //-- Reset product list cache
            CacheWrapper::resetCache(Auth::id(), 'product');
            if ($result === 'success') {

                $arrThumbnails = ProductRepository::getThumbnails($product);
                $data = [
                    'product' => $product,
                    'thumbnails' => $arrThumbnails,
                ];
                return response()->json(compact('data'), 200);
            } else {
                $data = $result;
                return response()->json(compact('data'), 422);
            }
        } else {
            return Http::notAuthorized($user_id);
        }
    }

    /**
     * @param $user_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($user_id, $id)
    {
        try {
            $product = Product::where('user_id', $user_id)->where('id', $id)->first();
            if ($product != null) {
                $result = ProductRepository::destroy($id);

                if ($result === 'success') {
                    $data = "Product with ID " . $id . " and all relevant data were successfully deleted.";
                    return response()->json(compact('data'), 200);
                } else {
                    $data = $result;
                    return response()->json(compact('data'), 500);
                }

            } else {
                return Http::notFound($id, 'product');
            }

        } catch (\Exception $e) {
            $data = $e->getMessage();
            return response()->json(compact('data'), 500);
        }
    }


    /**
     * @param $data
     * @param bool $update
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validator($data, $update = false)
    {
        $arrRules = [
            'name' => 'required',
            'case_count' => 'required|integer',
            'size' => 'required|integer',
            'brand' => 'required',
        ];

        $arrExraCreateRules = [
            'file' => 'required',
            'categories' => 'required',
        ];

        if (!$update) {
            $arrRules = array_merge($arrRules, $arrExraCreateRules);
        }
        return Validator::make($data, $arrRules);
    }

}