<?php
/**
 * Created by PhpStorm.
 * User: Maxim.Narushevich
 * Date: 22.10.2018
 * Time: 12:39
 */

namespace App\Http\Repositories;


use App\Category;
use App\User;
use Illuminate\Support\Facades\Cache;

class CategoryRepository
{
    /**
     * @param User $user
     * @return mixed
     */
    static public function getAll(User $user){

        //-- Load products from cache if available
        $categories = Cache::tags(['category_' . $user->id])->get('products_list');

        if (!$categories) {
            $categories = Category::where('user_id', $user->id)->get();
            Cache::tags(['category_' . $user->id])->put('category_list', $categories, 22 * 60);
        }

        $arrSubCategories=[];
        if(!empty($categories)){
            foreach ($categories as $category){
                $subCategory = Category::where('parent',$category->id)->first();
                $arrSubCategories[$category->id]['name']=$subCategory!==null?$subCategory->name:0;
                $arrSubCategories[$category->id]['id']=$subCategory!==null?$subCategory->id:0;
            }
        }
        $result=[
            'categories'=>$categories,
            'arrSubCategories'=>$arrSubCategories,
        ];
        return $result;
    }
}