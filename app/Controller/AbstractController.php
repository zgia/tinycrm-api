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
     * 输出
     *
     * @param string $msg
     * @param int    $code
     * @param array  $data
     *
     * @return PsrResponseInterface
     */
    public function resp(string $msg = '', int $code = I_SUCCESS, array $data = [])
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
     * 成功
     *
     * @param string $msg
     * @param array  $data
     *
     * @return PsrResponseInterface
     */
    public function success(string $msg = '', array $data = [])
    {
        return $this->resp($msg, I_SUCCESS, $data);
    }

    /**
     * 失败
     *
     * @param string $msg
     * @param int    $code
     *
     * @return PsrResponseInterface
     */
    public function fail(string $msg = '', int $code = I_FAILURE)
    {
        return $this->resp($msg, $code, []);
    }
}
