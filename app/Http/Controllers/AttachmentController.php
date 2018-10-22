<?php

namespace App\Http\Controllers;


use App\Http\Repositories\AttachmentRepository;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        //-- Perform uploading of file
        $result=AttachmentRepository::uploadFile($request);
        echo $result;
    }

    /**
     * @param Request $request
     */
    public function ajaxDeleteAttachment(Request $request)
    {
        $attachment_id = $request->attachment_id;
        $arrData=AttachmentRepository::delete($attachment_id);

        header('Content-Type: application/json');
        echo json_encode(array(
            'result' => $arrData['result'],
            'error' => $arrData['error']
        ));
    }
}
