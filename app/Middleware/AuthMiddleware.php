<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\BusinessException;
use App\Helper\jwt\NeoJwtInterface;
use App\Helper\NeoLog;
use App\Service\UserService;
use Hyperf\HttpServer\Contract\RequestInterface as HttpRequest;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(ContainerInterface $container, HttpResponse $response, HttpRequest $request)
    {
        // zGia! 读源码
        dump('*******************************', $response);
        $this->container = $container;
        /* @var \Hyperf\HttpServer\Response */
        $this->response = $response;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // zGia! 读源码
        dump('*******************************', $this->response);

        $validToken = null;
        $this->container->set('current_token_expired', 0);

        if ($this->request->getPathInfo() === '/sign/auth') {
            $validToken = true;
        }

        $validToken = true;

        if (! $validToken) {
            try {
                $authorization = $request->getHeader('authorization')[0] ?? '';
                NeoLog::info('AuthMiddleware', $authorization);

                if ($authorization) {
                    $jwt = $this->container->get(NeoJwtInterface::class);

                    $validToken = $jwt->authenticate('Authorization: ' . $authorization);
                    NeoLog::info('AuthMiddleware', $validToken);

                    $userid = (int) $validToken['userId'];

                    if ($userid) {
                        $user = UserService::getUser($userid);

                        if ($user) {
                            $this->container->set('current_user', $user);
                            $this->container->set('current_user_id', $userid);
                            $this->container->set('current_token_expired', $validToken['exp']);
                        } else {
                            $userid = 0;
                        }
                    }

                    if (! $userid) {
                        $validToken = null;
                        throw new BusinessException('无效Token。');
                    }
                }
            } catch (BusinessException $ex) {
                NeoLog::error('AuthMiddleware', $ex->getMessage());
            }
        }

        if ($validToken) {
            debug($request);
            $res = $handler->handle($request);
            debug($res);
            return $res;
        }

        return $this->response->json(
            [
                'code' => I_FAILURE,
                'msg' => '登录过期，请重新登录。',
                'data' => $this->request->getServerParams(),
            ]
        );
    }
}
