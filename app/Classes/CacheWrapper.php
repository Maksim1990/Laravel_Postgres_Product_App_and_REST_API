<?php
/**
 * Created by PhpStorm.
 * User: Maxim.Narushevich
 * Date: 22.10.2018
 * Time: 14:20
 */

namespace App\Classes;

use Illuminate\Support\Facades\Cache;

class CacheWrapper
{
    /**
     * @param $id
     * @param $type
     */
    static public function resetCache($id, $type)
    {
        //-- Flush specified cache for current user
        Cache::tags($type . '_' . $id)->flush();
    }

}