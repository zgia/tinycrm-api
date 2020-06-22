<?php

declare(strict_types=1);

namespace App\Helper;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;

/**
 * Class Log.
 *
 * @method void debug(string $message, $context)
 * @method void info(string $message, $context)
 * @method void notice(string $message, $context)
 * @method void warning(string $message, $context)
 * @method void error(string $message, $context)
 * @method void crit(string $message, $context)
 * @method void alert(string $message, $context)
 * @method void emerg(string $message, $context)
 */
class NeoLog
{
    /**
     * @param $name
     * @param $arguments
     */
    public static function __callStatic($name, $arguments)
    {
        static::get()->{$name}($arguments[0], (array) $arguments[1]);
    }

    /**
     * @param string $name
     *
     * @return \Psr\Log\LoggerInterface
     */
    public static function get(string $name = 'app')
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name);
    }
}
