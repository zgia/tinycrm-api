<?php

declare(strict_types=1);

namespace App\Helper;

use Hyperf\Redis\Redis;

/**
 * Class NeoRedis
 */
class NeoRedis
{
    /**
     * @return Redis
     */
    public static function getRedis()
    {
        return container()->get(Redis::class);
    }

    /**
     * 获取
     *
     * @param string $key
     *
     * @return null|array
     */
    public static function get($key)
    {
        $value = static::getRedis()->get($key);

        $value && $value = json_decode($value, true, 512, JSON_BIGINT_AS_STRING);

        return $value;
    }

    /**
     * 写入
     *
     * @see https://github.com/phpredis/phpredis#set
     *
     * @param string    $key
     * @param mixed     $value
     * @param array|int $expired 有效期，秒；或者NX|XX数组，['nx', 'ex'=>60]
     *
     * @return bool
     */
    public static function set($key, $value, $expired = 0)
    {
        $value = json_encode($value);

        if (is_array($expired) && $expired) {
            // nothing
        } else {
            $expired = max(0, (int) $expired);
        }

        $expired || $expired = [];

        return static::getRedis()->set($key, $value, $expired);
    }

    /**
     * 删除
     *
     * @param mixed ...$key
     *
     * @return int
     */
    public static function delete(...$key)
    {
        return static::getRedis()->del($key);
    }

    /**
     * 批量写
     *
     * @param array $data
     */
    public static function mset(array $data)
    {
        foreach (array_chunk($data, 100, true) as $chunk) {
            $chunk = array_map('json_encode', $chunk);

            static::getRedis()->mset($chunk);
        }
    }
}
