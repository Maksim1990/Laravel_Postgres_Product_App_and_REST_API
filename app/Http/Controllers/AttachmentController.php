<?php

namespace App\Http\Controllers;


use App\Attachment;
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxCheckResources(Request $request)
    {
        $product_id = $request->product_id;
        $status=true;
        $attachments = Attachment::where('product_id',$product_id)->get();

        if(count($attachments)==0){
            $status=false;
        }

        $result = [
            'status' => $status
        ];

        return response()->json([
            $result
        ]);
    }
}
