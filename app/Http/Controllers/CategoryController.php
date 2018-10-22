<?php

namespace App\Http\Controllers;

use App\Category;
use App\Interfaces\RedisInterface;
use App\ProductCategoryPivot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller implements RedisInterface
{

    public function index($id)
    {


        //-- Load products from cache if available
        $categories = Cache::tags(['category_' . Auth::id()])->get('products_list');

        if (!$categories) {
            $categories = Category::where('user_id', $id)->get();
            Cache::tags(['category_' . Auth::id()])->put('category_list', $categories, 22 * 60);
        }

        $arrSubCategories=[];
        if(!empty($categories)){
            foreach ($categories as $category){
                    $subCategory = Category::where('parent',$category->id)->first();
                    $arrSubCategories[$category->id]['name']=$subCategory!==null?$subCategory->name:0;
                    $arrSubCategories[$category->id]['id']=$subCategory!==null?$subCategory->id:0;
            }
        }

        return view('categories.index', compact('categories','arrSubCategories'));
    }

    /**
     * @param $id
     * @param $type
     * @return string
     */
    public function resetCache($id,$type)
    {
        //-- Flush cached category's cache for current user
        Cache::tags($type.'_' . $id)->flush();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxGetCategories(Request $request)
    {
        $product_id = $request->product_id;
        $strExcludeCat = $request->strExcludeCat;

        if (!empty($product_id)) {

        }
        $categories = Category::where('parent', 0)->where('name', '!=', $strExcludeCat)->get();
        $arrCategories = [];
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $arrCategories[] = $category->name;
            }
        }

        $result = [
            'arrCategories' => $arrCategories
        ];

        return response()->json([
            $result
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDeleteCategories(Request $request)
    {
        $arrCategoriesToDelete = json_decode($request->arrCategoriesToDelete);
        $success=true;

        if(!empty($arrCategoriesToDelete)){
            foreach ($arrCategoriesToDelete as $categorty){
                ProductCategoryPivot::where('category_id',$categorty)->delete();
                Category::where('id',$categorty)->delete();

            }
        }
        $result = [
            'success' => $success,
            'error' => '',
        ];

        //-- Reset category list cache
        $this->resetCache(Auth::id(),'category');

        return response()->json([
            $result
        ]);
    }
}
