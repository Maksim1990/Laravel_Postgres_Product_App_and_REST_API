<?php
/**
 * Created by PhpStorm.
 * User: Maxim.Narushevich
 * Date: 22.10.2018
 * Time: 13:58
 */

namespace App\Exceptions;


class Http
{

    /**
     * @param $id
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    static public function notFound($id,$type)
    {
        $data = lcfirst($type).' with ID ' . $id . ' not found';
        return response()->json(compact('data'), 404);
    }

    /**
     * @param $field
     * @return \Illuminate\Http\JsonResponse
     */
    static public function fieldRequired($field)
    {
        $data = 'Field ' . $field . ' id required';
        return response()->json(compact('data'), 422);
    }

    static public function notAuthorized($id)
    {
        $data = 'Not authorized to perform this action with user ID ' . $id;
        return response()->json(compact('data'), 401);
    }

}