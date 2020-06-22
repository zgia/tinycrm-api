<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\BusinessException;
use App\Helper\jwt\NeoJwtInterface;
use App\Helper\Utils;
use App\Model\User;
use Hyperf\Utils\Str;

/**
 * Class UserService.
 */
class UserService extends BaseService
{
    /**
     * 获取加密盐
     *
     * @param string $str    通过此串获取一个加密盐
     * @param int    $length 长度
     *
     * @return string 盐
     */
    public static function salt(?string $str = null, int $length = 6): string
    {
        if ($str) {
            return substr(base64_encode($str), 0, $length);
        }

        return Utils::salt($length);
    }

    /**
     * 生成用户的密码
     *
     * @param string $src  用户输入的原始串
     * @param string $salt 加密盐
     *
     * @return string 计算后得到的密码
     */
    public static function makeUserPassword(string $src, string $salt): string
    {
        if (Utils::isMD5Str($src)) {
            return md5($src . $salt);
        }

        return md5(md5($src) . $salt);
    }

    /**
     * 封禁用户
     *
     * @param int $userid 用户ID
     *
     * @return string 成功返回NULL，否则返回错误信息
     */
    public function banUser(int $userid)
    {
        // 检查当前用户是否存在
        $user = static::find($userid);
        if (! $user) {
            return '用户不存在。';
        }

        $user->usergroupid = USERGROUP_BAN;
        $user->save();

        // 保存日志
        $log = ['type' => 'user', 'action' => 'ban', 'id' => $userid];
        actionLog($log);

        return null;
    }

    /**
     * 更改某个用户的密码
     *
     * @param array $user 用户
     */
    public function changeUserPassword(array $user)
    {
        if (! $user || ! $user['userid']) {
            return;
        }

        $_user = static::find($user['userid']);
        $_user->password = static::makeUserPassword($user['password'], $user['salt']);
        $_user->save();
    }

    /**
     * 获取激活串
     *
     * @return string
     */
    public static function getActivationString()
    {
        return Str::random(5) . '-' . Str::random(6);
    }

    /**
     * 获取当前用户
     *
     * @param int $userid
     *
     * @return array
     */
    public static function renewJwt(int $userid = 0)
    {
        $token = '';
        // 还有一天过期时，自动更新
        if (container()->get('current_token_expired') < time() + 86400) {
            $user = static::find($userid);

            if ($user) {
                $jwt = container()->get(NeoJwtInterface::class);
                $token = $jwt->getUserToken('neo', $user->userid, $user->username);
            }
        }

        return $token;
    }

    /**
     * 获取某个用户
     *
     * @param int $userid
     *
     * @return null|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model|User
     */
    public static function find(int $userid = 0)
    {
        return User::query()->find($userid);
    }

    /**
     * 获取用户信息.
     *
     * @param int $userid 用户ID
     *
     * @return array
     */
    public static function getUser(int $userid = 0): array
    {
        $userid = (int) $userid;

        if ($userid) {
            $user = static::find($userid)->toArray();
        } else {
            $user = container()->get('current_user');

            if (! $user) {
                $user = User::emptyUser();
            }
        }

        return $user;
    }

    /**
     * 获取多个用户信息
     *
     * @param mixed $userids [1,2,3,4] 或者 123
     *
     * @return array
     */
    public static function getUsers($userids): array
    {
        $userids = (array) $userids;

        return User::query()->findMany($userids)->toArray();
    }

    /**
     * 根据条件获取用户信息
     *
     * @param array $cond
     *
     * @return null|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|User
     */
    public static function getUserByCond(array $cond)
    {
        return User::query()->where($cond)->first();
    }

    /**
     * 创建新用户/编辑用户
     *
     * @param array $user   用户信息
     * @param int   $userid 用户ID
     *
     * @throws BusinessException
     * @return User
     */
    public static function update(array $user, int $userid = 0)
    {
        // 检查当前用户是否存在
        if ($userid) {
            $editUserInfo = static::find($userid);
            if (! $editUserInfo->userid) {
                throw new BusinessException(__('用户不存在。'));
            }
        } else {
            $editUserInfo = new User();

            $editUserInfo->dateline = time();
        }

        $email = $user['email'];
        if ($email) {
            if ($err = static::checkEmail($email, $userid)) {
                throw new BusinessException($err);
            }

            $editUserInfo->email = $email;
        }

        if ($user['birthday']) {
            if (! preg_match('/\d{4}\.\d{1,2}\.\d{1,2}/', $user['birthday'])) {
                throw new BusinessException(__('错误的生日格式。'));
            }

            $editUserInfo->birthday = $user['birthday'];
        }

        // 检查用户名是否唯一
        // 无需检查
        $username = $user['username'];
        if ($username) {
            if (! static::isUniqueUsername($username, $userid)) {
                throw new BusinessException(__('用户名已被别人使用。'));
            }

            $editUserInfo->username = $username;
        }

        $mobile = $user['mobile'];
        if ($mobile) {
            if (! static::isUniqueMobile($mobile, $userid)) {
                throw new BusinessException(__('手机号已被别人使用。'));
            }
            $editUserInfo->mobile = $mobile;
        }

        // 更改密码或者添加用户
        if ($user['password']) {
            $editUserInfo->salt = static::salt();
            $editUserInfo->password = static::makeUserPassword($user['password'], $editUserInfo->salt);
        }

        if ($user['openid']) {
            $editUserInfo->openid = $user['openid'];
        }

        if ($user['usergroupid']) {
            $editUserInfo->usergroupid = $user['usergroupid'];
        }

        $editUserInfo->save();

        // 保存日志
        $log = [
            'type' => 'user',
            'action' => 'update',
            'id' => $userid,
            'from' => $user,
            'to' => $editUserInfo->toArray(),
        ];
        actionLog($log);

        return $editUserInfo;
    }

    /**
     * 检查Email是否正确.
     *
     * @param string $email  Email
     * @param int    $userid 用户ID
     *
     * @return string 成功返回NULL，否则返回错误信息
     */
    public static function checkEmail(string $email, int $userid)
    {
        if (empty($email)) {
            return __('请输入Email。');
        }
        // 检查email是否正确
        if (! Utils::isEmail($email)) {
            return __('Email格式错误。');
        }

        // 检查email是否唯一
        if (! static::isUniqueEmail($email, $userid)) {
            return __('Email已经被别人使用。');
        }

        return '';
    }

    /**
     * @param array $cond
     * @param int   $userid
     *
     * @return bool
     */
    public static function isUnique(array $cond, int $userid = 0)
    {
        $user = static::getUserByCond($cond);

        // 查无此人
        if (! $user || ! $user->userid) {
            return true;
        }

        // 如果有用户，则检查是否用户自己
        if ($userid && $user->userid == $userid) {
            return true;
        }
        return false;
    }

    /**
     * 注册、修改账户信息时，检查用户输入的email是否唯一。
     *
     * @param string $email  email
     * @param int    $userid 用户ID
     *
     * @return bool true表示唯一
     */
    public static function isUniqueEmail(string $email, int $userid = 0)
    {
        return static::isUnique(compact('email'), $userid);
    }

    /**
     * 修改账户信息时，检查用户输入的用户名是否唯一。
     *
     * @param string $username 用户名
     * @param int    $userid   用户ID
     *
     * @return bool true表示唯一
     */
    public static function isUniqueUsername(string $username, int $userid = 0)
    {
        return static::isUnique(compact('username'), $userid);
    }

    /**
     * 注册手机用户时，检查用户输入的手机号是否唯一。
     *
     * @param string $mobile mobile
     * @param int    $userid 用户ID
     *
     * @return bool true表示唯一
     */
    public static function isUniqueMobile(string $mobile, int $userid = 0)
    {
        return static::isUnique(compact('mobile'), $userid);
    }
}
