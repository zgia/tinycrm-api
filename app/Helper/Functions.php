<?php

declare(strict_types=1);

use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Utils\ApplicationContext;

/**
 * 获取容器实例
 *
 * @throws \RuntimeException
 * @return \Psr\Container\ContainerInterface|string
 */
function container()
{
    if (ApplicationContext::hasContainer()) {
        $container = ApplicationContext::getContainer();
    } else {
        $container = new Container((new DefinitionSourceFactory(true))());

        if (! $container instanceof \Psr\Container\ContainerInterface) {
            throw new \RuntimeException('The dependency injection container is invalid.');
        }
        ApplicationContext::setContainer($container);
    }

    return $container;
}

/**
 * 当前用户ID
 *
 * @return string
 */
function currentUserId()
{
    return \container()->get('current_user_id') ?? '';
}

/**
 * 当前用户ID
 *
 * @return string
 */
function currentPage()
{
    return max(1, (int) \container()->get('current_page'));
}

/**
 * 系统设置项会添加一个config前缀
 *
 * @param string $key
 *
 * @return string
 */
function getOptionCacheKey(string $key): string
{
    return 'config:' . $key;
}

/**
 * 返回某个系统设置项.
 *
 * @param string $key     系统设置的某个项
 * @param string $default 项的缺省值
 *
 * @return null|string 如果这个项目不存在，则返回NULL
 */
function getOption(string $key, $default = null)
{
    $val = \App\Helper\NeoRedis::get(getOptionCacheKey($key));

    return is_null($val) ? $default : $val;
}

/**
 * 获取基于某个时区的Carbon对象
 *
 * @param string $tz
 * @param bool   $immutable
 *
 * @return \Carbon\Carbon|\Carbon\CarbonImmutable|\Carbon\CarbonInterface
 */
function carbon(?string $tz = null, ?bool $immutable = false): Carbon\CarbonInterface
{
    $tz || $tz = getDatetimeZone();

    return $immutable ? \Carbon\CarbonImmutable::now($tz) : \Carbon\Carbon::now($tz);
}

/**
 * 获取时区
 *
 * @return string
 */
function getDatetimeZone(): string
{
    if (defined('DATETIME_ZONE') && DATETIME_ZONE) {
        return DATETIME_ZONE;
    }

    return date_default_timezone_get() ?: 'UTC';
}

/**
 * 获取时区的偏移，单位：秒
 *
 * @return int
 */
function getTimezoneOffset(): int
{
    if (defined('TIMEZONE_OFFSET') && TIMEZONE_OFFSET) {
        return TIMEZONE_OFFSET;
    }

    return timezone_offset_get(timezone_open(getDatetimeZone()), date_create());
}

/**
 * 将一个时间串转换为UTC时间的UNIX时间戳
 *
 * @param string $str  时间串
 * @param int    $time 时间戳
 *
 * @return int
 */
function stringToUtcTime(string $str, int $time = 0): int
{
    $time || $time = time();
    $t = strtotime($str, $time);

    return $t ? $t - getTimezoneOffset() : 0;
}

/**
 * 按照预订格式显示时间.
 *
 * @param string $format    格式
 * @param int    $timestamp 时间
 * @param int    $yestoday  时间显示模式：0：标准的年月日模式，1：今天/昨天模式，2：1分钟，1小时，1天等更具体的模式
 *
 * @return string 格式化后的时间串
 */
function formatDate(string $format = 'Ymd', int $timestamp = 0, int $yestoday = 0): string
{
    $carbon = carbon();

    if ($timestamp) {
        $microsecond = $carbon->microsecond;
        $carbon->setTimestamp($timestamp)
            ->setMicrosecond($microsecond);
    } else {
        $timestamp = $carbon->timestamp;
    }

    $timenow = time();

    if ($yestoday == 0) {
        $returndate = $carbon->format($format);
    } elseif ($yestoday == 1) {
        if (date('Y-m-d', $timestamp) == date('Y-m-d', $timenow)) {
            $returndate = __('Today');
        } elseif (date('Y-m-d', $timestamp) == date('Y-m-d', $timenow - 86400)) {
            $returndate = __('Yesterday');
        } else {
            $returndate = $carbon->format($format);
        }
    } else {
        $timediff = $timenow - $timestamp;

        if ($timediff < 0) {
            $returndate = $carbon->format($format);
        } elseif ($timediff < 60) {
            $returndate = __('1 minute before');
        } elseif ($timediff < 3600) {
            $returndate = sprintf(__('%d minutes before'), intval($timediff / 60));
        } elseif ($timediff < 7200) {
            $returndate = __('1 hour before');
        } elseif ($timediff < 86400) {
            $returndate = sprintf(__('%d hours before'), intval($timediff / 3600));
        } elseif ($timediff < 172800) {
            $returndate = __('1 day before');
        } elseif ($timediff < 604800) {
            $returndate = sprintf(__('%d days before'), intval($timediff / 86400));
        } elseif ($timediff < 1209600) {
            $returndate = __('1 week before');
        } elseif ($timediff < 3024000) {
            $returndate = sprintf(__('%d weeks before'), intval($timediff / 604900));
        } elseif ($timediff < 15552000) {
            $returndate = sprintf(__('%d months before'), intval($timediff / 2592000));
        } else {
            $returndate = $carbon->format($format);
        }
    }

    return $returndate;
}

/**
 * 格式化时间，长类型：Y-m-d H:i:s.
 *
 * @param int $timestamp 时间
 *
 * @return string
 */
function formatLongDate(int $timestamp = 0): string
{
    return formatDate('Y-m-d H:i:s', $timestamp);
}

/**
 * 翻译
 *
 * @param string $str 待翻译的字符串
 *
 * @return string
 */
function __(string $str): string
{
    return $str;
}

/**
 * 记录操作日志
 *
 * @param $data
 *
 * @return int
 */
function actionLog($data)
{
    return \App\Service\SystemService::actionLog($data);
}

/**
 * 获取某个断点的堆栈
 *
 * @param int $index
 * @param int $limit
 *
 * @return array
 */
function getDebugBacktrace(int $index = 0, int $limit = 0)
{
    $backtrace = debug_backtrace((PHP_VERSION_ID < 50306) ? 2 : DEBUG_BACKTRACE_IGNORE_ARGS, $limit);

    return $index ? $backtrace[$index] : $backtrace;
}

/**
 * 打印变量
 *
 * @param mixed ...$args
 */
function debug(...$args)
{
    if (empty($args)) {
        return;
    }

    $calledFrom = getDebugBacktrace();
    unset($calledFrom[0]);

    $errors = '[' . microtime(true) . ']';
    foreach ($args as $arg) {
        $errors .= print_r($arg, true) . PHP_EOL;
    }

    $lines = [];
    foreach ($calledFrom as $from) {
        $from['file'] = str_replace(BASE_PATH, '', $from['file']);
        $lines[] = "Func:{$from['function']} File:{$from['file']} Line:{$from['line']}";
    }

    echo $errors, PHP_EOL, '---------------', PHP_EOL, implode(PHP_EOL, $lines), PHP_EOL, '---------------', PHP_EOL, PHP_EOL;
}
