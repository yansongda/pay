<?php

namespace Yansongda\Pay\Exception;

use Psr\Container\ContainerExceptionInterface;
use Throwable;

class ContainerException extends Exception implements ContainerExceptionInterface
{
    /**
     * Bootstrap.
     *
     * @param string $message
     * @param int    $code
     * @param array  $extra
     */
    public function __construct($message = '', $extra = [], $code = self::CONTAINER_ERROR, Throwable $previous = null)
    {
        parent::__construct($message, $extra, $code, $previous);
    }
}
