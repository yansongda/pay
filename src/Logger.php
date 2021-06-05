<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Throwable;
use Yansongda\Pay\Contract\LoggerInterface;

/**
 * @method static void emergency($message, array $context = [])
 * @method static void alert($message, array $context = [])
 * @method static void critical($message, array $context = [])
 * @method static void error($message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void info($message, array $context = [])
 * @method static void debug($message, array $context = [])
 * @method static void log($message, array $context = [])
 */
class Logger
{
    public static function __callStatic(string $method, array $args): void
    {
        try {
            $class = Pay::get(LoggerInterface::class);
        } catch (Throwable $e) {
            return;
        }

        forward_static_call_array([$class, $method], $args);
    }
}
