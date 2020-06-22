<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::addRoute('GET','/sign/auth','App\Controller\SignController@auth');

// 客户
Router::addGroup(
    '/api/member',
    function () {
        Router::addRoute('GET', '/index', 'App\Controller\MemberController@index');
        Router::addRoute('GET', '/statuslist', 'App\Controller\MemberController@statuslist');
        Router::addRoute('GET', '/view[/[{memberid:\d+}]]', 'App\Controller\MemberController@view');
        Router::addRoute('GET', '/edit[/[{memberid:\d+}]]', 'App\Controller\MemberController@edit');
        Router::addRoute('POST', '/update[/[{memberid:\d+}]]', 'App\Controller\MemberController@update');
        Router::addRoute('GET', '/family/{memberid:\d+}', 'App\Controller\MemberController@family');
        Router::addRoute('POST', '/updatefamily', 'App\Controller\MemberController@updatefamily');
        Router::addRoute('DELETE', '/delete/{memberid:\d+}', 'App\Controller\MemberController@delete');
    }
);
