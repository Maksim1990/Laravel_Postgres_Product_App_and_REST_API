<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Repositories\CategoryRepository;
use App\Interfaces\RedisInterface;
use App\ProductCategoryPivot;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller implements RedisInterface
{

    public function index($id)
    {

        $user=User::find($id);
        $arrData=CategoryRepository::getAll($user);

        $categories=$arrData['categories'];
        $arrSubCategories=$arrData['arrSubCategories'];

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
        $strExcludeCat = $request->strExcludeCat;

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
