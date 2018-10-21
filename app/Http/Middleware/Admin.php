<?php

namespace App\Http\Middleware;

use App\Attachment;
use App\Config\Config;
use Closure;
use Illuminate\Support\Facades\Auth;
use Croppa;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->removeDeprecatedAttachments();
        if (Auth::check()) {
            return $next($request);
        }
        return redirect('/norights');

    }


    public function removeDeprecatedAttachments()
    {
        $old_attachments = Attachment::where('product_id', 0)->get();
        if (!empty($old_attachments)) {
            foreach ($old_attachments as $attachment) {
                if (file_exists(public_path() . $attachment->path)) {
                    Croppa::delete($attachment->path);
                }

                //-- Delete video thumbnails
                if (in_array($attachment->extension, Config::VIDEO_EXTENSIONS)) {
                    $thumbnail = getVideoThumbnail($attachment->name);
                    if (file_exists(public_path() . $thumbnail)) {
                        unlink(public_path() . $thumbnail);
                    }
                }
                $attachment->delete();
            }

        }
    }


}
