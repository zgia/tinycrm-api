<?php

declare(strict_types=1);

namespace App\Helper\jwt;

use Psr\Container\ContainerInterface;

/**
 * HTTP JWT 验证
 */
class NeoJwtFactory
{
    // 实现一个 __invoke() 方法来完成对象的生产，方法参数会自动注入一个当前的容器实例
    public function __invoke(ContainerInterface $container)
    {
        $config = [
            'secretkey' => env('JWT_SECRETKEY', ''),
            'algorithm' => env('JWT_ALGORITHM', 'HS512'),
            'expired_time' => env('JWT_EXPIRED_TIME', 518400),
            'interval_time' => env('JWT_INTERVAL_TIME', 0),
        ];

        return make(NeoJwt::class, compact('config'));
    }
}
