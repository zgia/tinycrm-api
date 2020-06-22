<?php

declare(strict_types=1);

namespace App\Helper;

use App\Exception\BusinessException;
use Hyperf\Guzzle\ClientFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Weixin
 */
class WeixinFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var int
     */
    private $errCode = 0;

    /**
     * @var string
     */
    private $errMsg = '';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 获取某次请求的错误
     *
     * @return array
     */
    public function getError(): array
    {
        return ['code' => $this->errCode, 'msg' => $this->errMsg];
    }

    /**
     * 重置错误
     */
    public function unsetError()
    {
        $this->errCode = 0;
        $this->errMsg = '';
    }

    /**
     * 登录凭证校验
     *
     * @param string $code
     *
     * @return array {openid,session_key,unionid,errcode,errmsg}
     */
    public function getAuthCode2Session($code)
    {
        if (! $code) {
            return [];
        }

        $this->unsetError();

        // errcode
        // -1	系统繁忙，此时请开发者稍候再试
        // 0	请求成功
        // 40029	code 无效
        // 45011	频率限制，每个用户每分钟100次

        return $this->httpRequest(
            'get',
            'https://api.weixin.qq.com/sns/jscode2session?appid=' . env('WEIXIN_APPID', '') . '&secret=' . env('WEIXIN_APPSECRET', '') . '&js_code=' . $code . '&grant_type=authorization_code'
        );
    }

    /**
     * 发起HTTP请求
     *
     * @param string $verb
     * @param string $url
     * @param array  $options
     * @param bool   $origin  返回数据是否需要json_decode
     *
     * @throws BusinessException
     * @return null|mixed
     */
    public function httpClient($verb, $url, array $options = [], $origin = false)
    {
        $options['timeout'] || $options['timeout'] = 10;

        $response = null;
        try {
            $client = $this->container->get(ClientFactory::class)->create($options);

            /**
             * @var ResponseInterface $result
             */
            $result = $client->{$verb}($url, $options);

            $response = $result->getBody()->getContents();

            return $origin ? $response : json_decode($response, true);
        } catch (\Throwable $ex) {
            NeoLog::error('weixin', ['httpClient', $ex->getMessage(), $ex->getCode()]);

            throw new BusinessException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * 发起HTTP请求
     *
     * @param string $verb   post || get
     * @param string $url    URL
     * @param array  $params 参数
     * @param bool   $origin 返回数据是否需要json_decode
     *
     * @return array|string
     */
    public function httpRequest($verb, $url, array $params = [], $origin = false)
    {
        if (strpos($url, 'http') !== 0) {
            $url = static::url($url);
        }

        NeoLog::info('weixin', $url);

        $result = $this->httpClient($verb, $url, $params, $origin);
        NeoLog::info('weixin', $result);

        return $origin ? $result : $this->processResult($result);
    }

    /**
     * 生成带Token的URL
     *
     * @param $url
     *
     * @return string
     */
    protected function url($url)
    {
        return 'https://api.weixin.qq.com/cgi-bin' . $url . (strpos(
            $url,
            '?'
        ) === false ? '?' : '&') . 'access_token=' . NeoRedis::get('wx_access_token');
    }

    /**
     * 处理微信返回的消息
     *
     * @param $result
     *
     * @return array
     */
    protected function processResult($result)
    {
        if ($result) {
            if ($result['errcode']) {
                $this->errCode = $result['errcode'];
                $this->errMsg = $result['errmsg'];

                return [];
            }

            return $result;
        }

        return [];
    }
}
