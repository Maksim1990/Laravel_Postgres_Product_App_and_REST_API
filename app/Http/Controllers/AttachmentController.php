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
                $name = time() . "_" . $file->getClientOriginalName();

                request()->file('file')->storeAs(
                    'public/upload/images/' . Auth::id(), $name
                );

                $attachment = Attachment::create([
                    'user_id' => Auth::id(),
                    'product_id' => $request->product_id,
                    'name' => $name,
                    'size' => $file->getClientSize(),
                    'extension' => $extension,
                    'path' => 'upload/images/' . Auth::id() . '/' . $name
                ]);

            } else {
                $result = "Max allowed limit for file is 2 MB!";
            }
        } else {
            $result = 'Error of file format. Only following formats are allowed: ' . ['formats' => implode(",", $arrAllowedExtension)];
        }

        echo $result;
    }

    public function ajaxDeleteAttachment(Request $request)
    {
        $attachment_id = $request->attachment_id;
        $strError = "";
        $result = "success";

        $attachment = Attachment::find($attachment_id);
        if (!empty($attachment)) {

            if (file_exists(storage_path('/app/public/' . $attachment->path))) {
                unlink(storage_path('/app/public/' . $attachment->path));
            }
            $attachment->delete();
        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $result,
            'error' => $strError
        ));
    }
}
