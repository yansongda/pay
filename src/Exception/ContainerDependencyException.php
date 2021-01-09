<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class ContainerDependencyException extends ContainerException
{
    /**
     * Bootstrap.
     */
    public function __construct(string $message = 'Dependency Resolve Error', array $extra = [], int $code = self::CONTAINER_DEPENDENCY_ERROR, Throwable $previous = null)
    {
        parent::__construct($message, $extra, $code, $previous);
    }
}
