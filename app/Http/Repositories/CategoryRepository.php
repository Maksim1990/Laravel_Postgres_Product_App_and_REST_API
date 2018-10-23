<?php
/**
 * Created by PhpStorm.
 * User: Maxim.Narushevich
 * Date: 22.10.2018
 * Time: 12:39
 */

namespace App\Http\Repositories;


use App\Category;
use App\CategorySubcategoryPivot;
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
                $arrParent=CategorySubcategoryPivot::where('parent_id',$category->id)->where('parent_id','!=',0)->get();
                //dd($arrParent);
                $arrTemp=[];
                if(count($arrParent)>0){

                    foreach ($arrParent as $parent){
                        $subCategory=Category::find($parent->category_id);
                        $arrTemp['name'][]=$subCategory->name;
                        $arrTemp['id'][]=$subCategory->id;
                    }
                    $arrSubCategories[$category->id]['name']=implode(",",array_unique($arrTemp['name']));
                    $arrSubCategories[$category->id]['id']=$arrTemp['id'];
                }else{
                    $arrSubCategories[$category->id]['id']=[];
                }
            }
        }
       // dd($arrSubCategories);
        $result=[
            'categories'=>$categories,
            'arrSubCategories'=>$arrSubCategories,
        ];
        return $result;
    }
}