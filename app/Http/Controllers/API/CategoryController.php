<?php

namespace App\Http\Controllers\API;

use App\Category;
use App\Exceptions\Http;
use App\Http\Repositories\CategoryRepository;
use App\ProductCategoryPivot;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController
{
    /**
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories($user_id)
    {
        //-- Load products from cache if available
        $user = User::find($user_id);
        $arrData = CategoryRepository::getAll($user);
        $categories = $arrData['categories'];
        $data = $categories;

        return response()->json(compact('data'), 200);
    }

    /**
     * @param $user_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($user_id, $id)
    {
        $category = Category::where('user_id', $user_id)->where('id', $id)->first();

        if ($category != null) {

            $data = [
                'category' => $category,
            ];

            return response()->json(compact('data'), 200);
        } else {
            return Http::notFound($id);
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
        $input = $request->all();
        $category = Category::where('user_id', $user_id)->where('id', $id)->first();
        if ($category != null) {
            $category->update($input);
            $category->save();
            $data = "Category with ID " . $id . " was successfully updated.";
            return response()->json(compact('data'), 200);
        } else {
            return Http::notFound($id);
        }
    }


    /**
     * @param $user_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($user_id, Request $request)
    {
        if ($user_id == Auth::id()) {
            $input = $request->all();

            if(isset($input['name']) || !empty($input['name'])){
                $category = Category::create([
                    'user_id'=>!empty($request->user_id)?$request->user_id:Auth::id(),
                    'name'=>$input['name'],
                    'parent'=>isset($input['parent'])?$input['parent']:0,
                ]);

                $data = "Category with ID " . $category->id . " was successfully created.";
                return response()->json(compact('data'), 200);
            }else{
                return Http::fieldRequired('name');
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
            $category = Category::where('user_id', $user_id)->where('id', $id)->first();
            if ($category != null) {
                ProductCategoryPivot::where('category_id', $id)->delete();
                $category->delete();

                $data = 'Category with ID ' . $id . ' and relevant data was deleted';
                return response()->json(compact('data'), 200);
            } else {
                return Http::notFound($id);
            }

        } catch (\Exception $e) {
            $data = $e->getMessage();
            return response()->json(compact('data'), 500);
        }
    }

}