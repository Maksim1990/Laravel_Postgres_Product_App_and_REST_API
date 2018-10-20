<?php

namespace App\Http\Controllers;

use App\Attachment;
use App\Config\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Croppa;
use Pawlox\VideoThumbnail\Facade\VideoThumbnail;

class AttachmentController extends Controller
{

    public $folder = '/uploads/'; // add slashes for better url handling

    public function store(Request $request)
    {
        $result = "success";

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        if (in_array($extension, Config::IMAGES_EXTENSIONS)) {
            if (!($file->getClientSize() > 21000000)) {
                $name = time() . "_" . $file->getClientOriginalName();

                $attachment = Attachment::create([
                    'user_id' => Auth::id(),
                    'product_id' => $request->product_id,
                    'name' => $name,
                    'size' => $file->getClientSize(),
                    'extension' => $extension,
                    'path' => $this->folder . $name
                ]);

                //-- Temporary upload for public derictory in order generate thumbnails
                $file->move('uploads', $name);
            } else {
                $result = "Max allowed limit for file is 20 MB!";
            }
        }

        //-- UPLOAD VIDEO CONTENT
        if (in_array($extension, Config::VIDEO_EXTENSIONS)) {
            if (!($file->getClientSize() > 51000000)) {
                $name = time() . "_" . $file->getClientOriginalName();

                $attachment = Attachment::create([
                    'user_id' => Auth::id(),
                    'product_id' => $request->product_id,
                    'name' => $name,
                    'size' => $file->getClientSize(),
                    'extension' => $extension,
                    'type' => 'video',
                    'path' => $this->folder . $name
                ]);

                //-- Temporary upload for public derictory in order generate thumbnails
                $file->move('uploads', $name);

                $width = 400;
                $height = 400;
                $second=2;
                $thumbnailName=$name.'_'.$width.'x'.$height.'.jpg';

                VideoThumbnail::createThumbnail(public_path('uploads/' . $name), public_path('uploads/thumbnails/'), $thumbnailName, $second, $width, $height);


            } else {
                $result = "Max allowed limit for file is 50 MB!";
            }
        }

//        else {
//            $result = 'Error of file format. Only following formats are allowed: ' . ['formats' => implode(",", Config::VIDEO_EXTENSIONS)];
//        }

        echo $result;
    }

    public function ajaxDeleteAttachment(Request $request)
    {
        $attachment_id = $request->attachment_id;
        $strError = "";
        $result = "success";

        $attachment = Attachment::find($attachment_id);
        if (!empty($attachment)) {

            if(file_exists(public_path().$attachment->path)){
                Croppa::delete($attachment->path);
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
