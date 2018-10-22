<?php
/**
 * Created by PhpStorm.
 * User: Maxim.Narushevich
 * Date: 22.10.2018
 * Time: 10:52
 */

namespace App\Interfaces;


interface RedisInterface
{

    /**
     * Reset Redis Cache
     *
     * @param $id
     * @param $type
     * @return mixed
     */
    public function resetCache($id, $type);
}