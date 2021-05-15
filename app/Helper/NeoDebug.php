<?php

declare(strict_types=1);

namespace App\Helper;

class NeoDebug
{
    /**
     * 选择使用 print_r 还是 var_dump
     */
    public static function dump(...$args)
    {
        $first = array_shift($args);

        $hr = PHP_EOL . '-------------------------' . PHP_EOL;

        echo 'Time: ' . formatLongDate() . $hr;
        
        foreach ($args as $arg) {
            echo '|==> ',
            $first === 'print' ? print_r($arg, true) : var_dump($arg);
            echo PHP_EOL;
        }

        $lines = NeoDebug::getTracesAsString(array_slice(NeoDebug::getTraces(), 2));
        echo $hr, implode(PHP_EOL, $lines);
    }

    /**
     * 获取某个断点的堆栈
     *
     * @param int $index
     * @param int $limit
     *
     * @return array
     */
    public static function getTraces(int $index = 0, int $limit = 0)
    {
        $backtraces = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit);

        foreach ($backtraces as &$trace) {
            if (is_array($trace['args'])) {
                foreach ($trace['args'] as &$arg) {
                    if (is_object($arg)) {
                        $arg = 'Object(' . get_class($arg) . ')';
                    } elseif (is_array($arg)) {
                        $arg = 'Array';
                    } elseif (is_string($arg)) {
                        $arg = "'" . addslashes($arg) . "'";
                    }
                }
            } else {
                $trace['args'] = [];
            }
        }

        return $index ? $backtraces[$index] : $backtraces;
    }

    /**
     * 以 Throwable::getTraceAsString() 的方式格式化将某个断点堆栈
     *
     * @param array $traces
     *
     * @return array
     */
    public static function getTracesAsString(array $traces)
    {
        $lines = [];
        foreach ($traces as $i => $trace) {
            $lines[] = "#{$i} " . static::traceToString($trace);
        }

        return $lines;
    }

    /**
     * 格式某条Trace
     *
     * @param array $trace
     *
     * @return string
     */
    public static function traceToString(array $trace)
    {
        $func = $trace['class'] . $trace['type'] . $trace['function'];

        return static::removeSysPath("{$trace['file']}({$trace['line']}): {$func}(" . implode(
            ', ',
            $trace['args']
        ) . ')');
    }

    /**
     * 移除系统路径
     *
     * @param string $path
     */
    public static function removeSysPath(string $path)
    {
        return str_ireplace(BASE_PATH, '', $path);
    }
}
