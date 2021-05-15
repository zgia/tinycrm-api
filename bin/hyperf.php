#!/usr/bin/env php
<?php

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

// 错误报告
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

date_default_timezone_set('Asia/Shanghai');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

require BASE_PATH . '/vendor/autoload.php';

// 一些业务常量
require BASE_PATH . '/app/Constants/Srv.php';

// zGia! 读源码
// 入口
// Self-called anonymous function that creates its own scope and keep the global namespace clean.
(function () {
    Hyperf\Di\ClassLoader::init();
    /** @var \Psr\Container\ContainerInterface $container */
    $container = require BASE_PATH . '/config/container.php';

    // $container: Hyperf\Di\Container
    // get的是: Hyperf\Framework\ApplicationFactory
    // 返回一个Symfony\Component\Console\Application实例
    // ApplicationFactory 41行

    /**
     * @var \Symfony\Component\Console\Application $application
     */
    $application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
    $application->run();
})();
