<?php

declare(strict_types=1);

namespace App\Helper\jwt;

use App\Exception\BusinessException;
use Firebase\JWT\JWT;

/**
 * HTTP JWT 验证
 */
class NeoJwt implements NeoJwtInterface
{
    // 验证间隔时间
    private $interval_time = 0;

    // 过期时间
    private $expired_time = 518400;

    // 加密串
    private $secretkey = '';

    // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
    private $algorithm = 'HS512';

    /**
     * HttpJWT constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (empty($config['secretkey'])) {
            throw new BusinessException('You must config secretkey for JWT encode.');
        }

        $this->secretkey = $config['secretkey'];
        $this->algorithm = $config['algorithm'] ?? 'HS512';
        $this->expired_time = $config['expired_time'] ?? 518400;
        $this->interval_time = $config['interval_time'] ?? 0;
    }

    /**
     * 验证
     *
     * @param string $authorization
     *
     * @throws BusinessException
     * @return array
     */
    public function authenticate(string $authorization)
    {
        [$jwt] = sscanf($authorization, 'Authorization: Bearer %s');

        // No token was able to be extracted from the authorization header
        if (! $jwt) {
            throw new BusinessException('HTTP/1.0 400 Bad Request', 400);
        }

        try {
            $authed = JWT::decode(
                $jwt,
                $this->secretkey,
                [$this->algorithm]
            );
            debug($authed);
            return ['userId' => $authed->uid, 'userName' => $authed->unm, 'exp' => $authed->exp];
        } catch (\Throwable $ex) {
            throw new BusinessException($ex->getMessage(), 401, $ex);
        }
    }

    /**
     * @param string $server
     * @param int    $userid
     * @param string $username
     *
     * @throws BusinessException
     * @return mixed
     */
    public function getUserToken($server, $userid, $username)
    {
        try {
            // Json Token Id: an unique identifier for the token
            $tokenId = base64_encode(random_bytes(32));
            // Issued at: time when the token was generated
            $issuedAt = time();
            // 项目请求没有间隔,登录后立刻验证
            $notBefore = $issuedAt + $this->interval_time;
            $expire = $notBefore + $this->expired_time;
            $issuer = $server;

            /*
             * Create the token as an array
             */
            $data = [
                'iat' => $issuedAt,
                'jti' => $tokenId,
                'iss' => $issuer,
                'nbf' => $notBefore,
                'exp' => $expire,
                'uid' => $userid,
                'unm' => $username,
            ];

            /*
             * Encode the array to a JWT string.
             * Second parameter is the key to encode the token.
             *
             * The output string can be validated at http://jwt.io/
             */

            return JWT::encode($data, $this->secretkey, $this->algorithm);
        } catch (\Throwable $ex) {
            throw new BusinessException($ex->getMessage(), $ex->getCode());
        }
    }
}
