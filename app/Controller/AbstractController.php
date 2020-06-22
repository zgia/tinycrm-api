<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;

    /**
     * 成功
     *
     * @param array  $data
     * @param string $msg
     * @param int    $code
     *
     * @return PsrResponseInterface
     */
    public function success(array $data = [], string $msg = '', int $code = I_SUCCESS)
    {
        $arr = ['code' => $code];

        if ($msg) {
            $arr['msg'] = $msg;
        }
        if ($data) {
            $arr['data'] = $data;
        }
        return $this->response->json($arr);
    }

    /**
     * 失败.
     *
     * @param string $msg
     * @param int    $code
     *
     * @return PsrResponseInterface
     */
    public function fail(string $msg = '', int $code = I_FAILURE)
    {
        return $this->success([], $msg, $code);
    }
}
