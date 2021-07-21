<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class ContainerDependencyException extends ContainerException
{
    /**
     * Bootstrap.
     *
     * @param mixed $extra
     */
    public function __construct(string $message = 'Dependency Resolve Error', int $code = self::CONTAINER_DEPENDENCY_ERROR, $extra = null, Throwable $previous = null)
    {
        parent::__construct($message, $code, $extra, $previous);
    }
}
