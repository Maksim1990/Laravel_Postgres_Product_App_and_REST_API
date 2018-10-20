<?php

namespace App\Config;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    const IMAGES_EXTENSIONS = array(
        'jpg','jpeg','png'
    );

    const VIDEO_EXTENSIONS = array(
        'mp4'
    );

}
