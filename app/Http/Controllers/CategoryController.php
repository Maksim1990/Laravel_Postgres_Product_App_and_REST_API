<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
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
        $categories = Category::where('parent', 0)->where('name','!=',$strExcludeCat)->get();
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
}
