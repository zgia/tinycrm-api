<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\BusinessException;
use App\Helper\jwt\NeoJwtInterface;
use App\Helper\NeoLog;
use App\Helper\WeixinFactory;
use App\Service\UserService;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Class SignController
 */
class SignController extends AbstractController
{
    /**
     * @return PsrResponseInterface
     */
    public function index()
    {
        $logined = $this->getWeappLoginSession($this->request->input('code', ''));

        NeoLog::info('login', $logined);

        if ($logined['errcode']) {
            return $this->success($logined['errmsg'], $logined['errcode'], $logined);
        }

        $user = UserService::getUserByCond(['openid' => $logined['openid']]);

        if (empty($user)) {
            try {
                $data = [
                    'username' => $logined['openid'],
                    'openid' => $logined['openid'],
                    'usergroupid' => USERGROUP_USER,
                ];

                $user = UserService::update($data);
            } catch (BusinessException $ex) {
                return $this->fail($ex->getMessage());
            }
        }

        if ($user) {
            $token = container()
                ->get(NeoJwtInterface::class)
                ->getUserToken('neo', $user->userid, $user->username);

            return $this->success(
                '欢迎来到' . getOption('websitename'),
                ['token' => $token, 'username' => $user['username']]
            );
        }

        return $this->fail('没有生成用户，请退出小程序重试');
    }

    /**
     * 获取当前用户的openid
     *
     * @return PsrResponseInterface
     */
    public function openid()
    {
        $logined = $this->getWeappLoginSession($this->request->input('code', ''));

        return $this->success('', ['openid' => $logined['openid']]);
    }

    /**
     * 验证Token是否存在
     */
    public function auth()
    {
        $userid = (int) $this->request->input('userid', 0);

        $token = UserService::renewJwt($userid);

        $data = [
            'token' => $token,
            'renew' => (bool) $token,
        ];

        return $this->success('成功登录', $data);
    }

    /**
     * 通过code获得用户微信信息
     *
     * @param null|string $code
     *
     * @return array
     */
    private function getWeappLoginSession(?string $code): array
    {
        // "session_key": "V5YufzIq+ud7jHGn9ZBUkw==",
        // "expires_in": 7200,
        // "openid": "o1-QC0VOnwHxBOKBmhG0tex3Cjnc",
        return container()->get(WeixinFactory::class)->getAuthCode2Session($code);
    }
}
