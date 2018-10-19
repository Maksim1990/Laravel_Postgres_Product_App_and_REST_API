<?php

namespace App\Http\Controllers;

use App\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttachmentController extends Controller
{
    public function store(Request $request)
    {
        $result = "success";
        $arrAllowedExtension = ['png', 'jpg', 'jpeg'];

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        if (in_array($extension, $arrAllowedExtension)) {
            if (!($file->getClientSize() > 2100000)) {
                $name = time() ."_".$file->getClientOriginalName();

                request()->file('file')->storeAs(
                    'public/upload/photos/', $name
                );

                Attachment::create([
                    'user_id' => Auth::id(),
                    'name' => $name,
                    'size' => $file->getClientSize(),
                    'extension' => $extension,
                    'path' => 'upload/' . Auth::id() . '/photos/' . $name
                ]);

            } else {
                $result = trans('images::messages.image_limit')." 2 MB!";
            }
        } else {
            $result = trans('images::messages.image_format_error',['formats'=>implode(",", $arrAllowedExtension)]);
        }

        echo $result;


    }
}
