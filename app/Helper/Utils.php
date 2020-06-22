<?php

declare(strict_types=1);

namespace App\Helper;

/**
 * Class Utils.
 */
class Utils
{
    /**
     * 缺省日期
     *
     * @param  ?string     $date
     * @return null|string
     */
    public static function zeroDate(?string $date = null)
    {
        $date || $date = '0000-00-00';

        return $date;
    }

    /**
     * 转换缺省日期
     *
     * @param  string      $date
     * @return null|string
     */
    public static function convertZeroDate(?string $date = null)
    {
        $date || $date = '';
        $date == '0000-00-00' && $date = '';

        return $date;
    }

    /**
     * 计算年龄
     *
     * @param string $birthday
     *
     * @return int
     */
    public static function age($birthday)
    {
        return formatDate('Y') - substr($birthday, 0, 4);
    }

    /**
     * 获取 ASCII表 中，从33到126之间的可见字符组成的长度为 $length 的随机串
     *
     * @param int $length 长度
     *
     * @return string 字符串
     */
    public static function salt(int $length = 6)
    {
        $salt = '';

        for ($i = 0; $i < $length; ++$i) {
            $salt .= chr(mt_rand(33, 126));
        }

        return $salt;
    }

    /**
     * Verifies that a string is an MD5 string
     *
     * @param string $md5 The MD5 string
     *
     * @return bool
     */
    public static function isMD5Str(string $md5)
    {
        return preg_match('#^[a-f0-9]{32}$#', $md5) ? true : false;
    }

    /**
     * HTTP URL 转换为 HTTPS URL.
     *
     * @param  string $url
     * @return mixed
     */
    public static function httpToHttps(string $url)
    {
        return str_replace('http://', 'https://', $url);
    }

    /**
     * Verifies that an email is valid.
     *
     * Does not grok i18n domains. Not RFC compliant.
     *
     * @param string $email email address to verify
     *
     * @return bool
     */
    public static function isEmail(string $email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
    }

    /**
     * 多行文字转换为数组
     *
     * @param string $str
     *
     * @return array
     */
    public static function linesToArray(string $str)
    {
        $str = preg_replace(['/\r\n|\r/', '/\n+/'], PHP_EOL, trim($str));

        return array_map('trim', explode(PHP_EOL, $str));
    }
}
