<?php

namespace App\Http\Controllers\API;

use App\Exceptions\Http;
use App\Http\Controllers\Controller;
use App\Http\Repositories\ProductRepository;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

}