<?php

declare(strict_types=1);

namespace App\Helper\jwt;

use App\Exception\BusinessException;
use Firebase\JWT\JWT;

/**
 * HTTP JWT 验证
 */
interface NeoJwtInterface
{
    /**
     * 验证
     *
     * @param string $authorization
     *
     * @throws BusinessException
     * @return array
     */
    public function authenticate(string $authorization);

    /**
     * @param string $server
     * @param int    $userid
     * @param string $username
     *
     * @throws BusinessException
     * @return mixed
     */
    public function getUserToken($server, $userid, $username);
}
