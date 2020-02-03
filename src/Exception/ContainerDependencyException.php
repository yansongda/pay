<?php

namespace Yansongda\Pay\Exception;

use Throwable;

class ContainerDependencyException extends ContainerException
{
    /**
     * Bootstrap.
     *
     * @param string $message
     * @param array  $extra
     * @param int    $code
     */
    public function __construct($message = 'Dependency Resolve Error', $extra = [], $code = self::CONTAINER_DEPENDENCY_ERROR, Throwable $previous = null)
    {
        parent::__construct($message, $extra, $code, $previous);
    }
}
