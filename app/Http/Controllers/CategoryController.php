<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function ajaxGetCategories(Request $request)
    {
        $attachment_id = $request->attachment_id;
        $strError = "";
        $result = "success";


        $availableTags = [
            "ActionScript",
            "AppleScript",
            "Asp",
        ];

        return response()->json([
            $availableTags
        ]);
    }
}
