<?php
/**
 * Created by PhpStorm.
 * User: Maxim.Narushevich
 * Date: 22.10.2018
 * Time: 15:42
 */

namespace App\Http\Repositories;


use App\Attachment;
use App\Config\Config;
use Illuminate\Support\Facades\Auth;
use Croppa;
use Pawlox\VideoThumbnail\Facade\VideoThumbnail;
use Illuminate\Http\Request;

class AttachmentRepository
{
    /**
     * @param $attachment_id
     * @return array|string
     */
    static public function delete($attachment_id)
    {
        $error = "";
        $result = "success";

        $attachment = Attachment::find($attachment_id);
        if (!empty($attachment)) {

            if (file_exists(public_path() . $attachment->path)) {
                Croppa::delete($attachment->path);
            }

            //-- Delete video thumbnails
            if (in_array($attachment->extension, Config::VIDEO_EXTENSIONS)) {
                $thumbnail = getVideoThumbnail($attachment->name);

                if (!empty($thumbnail) && file_exists(public_path() . $thumbnail)) {
                    unlink(public_path() . $thumbnail);
                }
            }
            if(!$attachment->delete()){
                $result = "Some error happened while deleting attachment";
            }
        }
        $result=[
            'error'=>$error,
            'result'=>$result
        ];
        return $result;
    }

    /**
     * @param Request $request
     * @param null $product_id
     * @return string
     */
    static public function uploadFile($request,$product_id=null)
    {
        $result = "success";
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        $attachments = Attachment::where('product_id', empty($product_id)?$request->product_id:$product_id)->get();
        if (count($attachments) < Config::ATTACHMENTS_ALLOWED) {
            if (in_array($extension, Config::IMAGES_EXTENSIONS)) {
                if (!($file->getClientSize() > 21000000)) {
                    $name = time() . "_" . $file->getClientOriginalName();
                    $maxID=Attachment::where('id','>',0)->orderBy('id','DESC')->limit(1)->first();
                    $attachment = Attachment::create([
                        'id'=>$maxID!=null?$maxID->id+1:1,
                        'user_id' => Auth::id(),
                        'product_id' => empty($product_id)?$request->product_id:$product_id,
                        'name' => $name,
                        'size' => $file->getClientSize(),
                        'extension' => $extension,
                        'path' => Config::UPLOAD_FOLDER . $name
                    ]);

                    //-- Temporary upload for public derictory in order generate thumbnails
                    $file->move('uploads', $name);
                } else {
                    $result = "Max allowed limit for image file is 20 MB!";
                }
            } elseif (in_array($extension, Config::VIDEO_EXTENSIONS)) {
                //-- UPLOAD VIDEO CONTENT
                if (!($file->getClientSize() > 51000000)) {
                    $name = time() . "_" . $file->getClientOriginalName();
                    $maxID=Attachment::where('id','>',0)->orderBy('id','DESC')->limit(1)->first();
                    $attachment = Attachment::create([
                        'id'=>$maxID!=null?$maxID->id+1:1,
                        'user_id' => Auth::id(),
                        'product_id' => empty($product_id)?$request->product_id:$product_id,
                        'name' => $name,
                        'size' => $file->getClientSize(),
                        'extension' => $extension,
                        'type' => 'video',
                        'path' => Config::UPLOAD_FOLDER . $name
                    ]);

                    //-- Temporary upload for public derictory in order generate thumbnails
                    $file->move('uploads', $name);

                    $width = 400;
                    $height = 400;
                    $second = 2;
                    $arrName = explode(".", $name);
                    $thumbnailName = $arrName[0] . '_' . $width . 'x' . $height . '.jpg';

                    VideoThumbnail::createThumbnail(public_path('uploads/' . $name), public_path('uploads/thumbnails/'), $thumbnailName, $second, $width, $height);


                } else {
                    $result = "Max allowed limit for video file is 50 MB!";
                }
            } else {
                $arrFormats = array_merge(Config::IMAGES_EXTENSIONS, Config::VIDEO_EXTENSIONS);
                $result = 'Error of file format. Only following formats are allowed: ' . implode(",", $arrFormats);
            }
        }else{
            $result = "Maximum ".Config::ATTACHMENTS_ALLOWED." attachments per product are allowed";
        }

        return $result;
    }
}