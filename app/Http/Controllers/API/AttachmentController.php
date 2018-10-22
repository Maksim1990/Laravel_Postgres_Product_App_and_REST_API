<?php

namespace App\Http\Controllers\API;

use App\Attachment;
use App\Exceptions\Http;
use App\Http\Repositories\AttachmentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AttachmentController extends Controller
{
    /**
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachments($user_id)
    {
        if ($user_id == Auth::id()) {
            //-- Load products from cache if available
            $attachments = Attachment::where('user_id', $user_id)->get();
            $data = $attachments;

            return response()->json(compact('data'), 200);
        } else {
            return Http::notAuthorized($user_id);
        }
    }

    /**
     * @param $user_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($user_id, $id)
    {
        if ($user_id == Auth::id()) {
            $attachment = Attachment::where('user_id', $user_id)->where('id', $id)->first();
            if ($attachment != null) {
                $data = $attachment;

                return response()->json(compact('data'), 200);
            } else {
                return Http::notFound($id, 'attachment');
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
        if ($user_id == Auth::id()) {
            $attachment = Attachment::where('user_id', $user_id)->where('id', $id)->first();
            if ($attachment != null) {
                $arrData=AttachmentRepository::delete($id);
                if($arrData['result']=='success'){
                    $data = "Attachment with ID " . $id . " was successfully deleted.";
                    return response()->json(compact('data'), 200);
                }else{
                    $data = $arrData['result'];
                    return response()->json(compact('data'), 422);
                }
            } else {
                return Http::notFound($id, 'attachment');
            }
        } else {
            return Http::notAuthorized($user_id);
        }
    }

    /**
     * @param $user_id
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCaption($user_id, Request $request, $id)
    {
        if ($user_id == Auth::id()) {
            $attachment = Attachment::where('user_id', $user_id)->where('id', $id)->first();
            if ($attachment != null) {
                if(isset($request->caption) && !empty($request->caption)){
                    $attachment->caption=$request->caption;
                    $attachment->save();
                    $data=$attachment;
                    return response()->json(compact('data'), 200);
                }else{
                    return Http::fieldRequired('caption');
                }
            } else {
                return Http::notFound($id, 'attachment');
            }
        } else {
            return Http::notAuthorized($user_id);

        }
    }
}
