<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

/**
 * 网络异常类.
 *
 * 用于处理网络请求相关的异常，如超时、连接失败、DNS解析失败等。
 */
class NetworkException extends Exception
{
    public function __construct(
        int $code = self::NETWORK_ERROR,
        string $message = '网络异常',
        mixed $extra = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $extra, $previous);
    }
}
