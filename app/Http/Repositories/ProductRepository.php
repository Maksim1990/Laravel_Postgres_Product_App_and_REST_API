<?php
/**
 * Created by PhpStorm.
 * User: Maxim.Narushevich
 * Date: 22.10.2018
 * Time: 12:04
 */

namespace App\Http\Repositories;
use App\Category;
use App\Product;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Croppa;

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


}