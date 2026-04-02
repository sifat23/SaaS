<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;

class RedisHandlers
{
    public static final function setAData (
        string $name,
        mixed $data
    ) {
        Redis::set($name, json_encode($data));
        Redis::expire($name, 3600);
    }

    public static final function getData (
        string $name
    ) {
        $result = new \stdClass();

        $data = Redis::get($name);

        if($data === null) {
            return null;
        }

        if($data) {
            $result = json_decode($data, false);
        }

        return $result;
    }

    public static final function delData (
        string $name
    ) {
        Redis::del($name);
    }
}
