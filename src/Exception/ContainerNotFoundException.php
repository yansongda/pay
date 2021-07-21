<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class ContainerNotFoundException extends ContainerException
{
    /**
     * Bootstrap.
     *
     * @param mixed $extra
     */
    public function __construct(string $message = 'Container Not Found', int $code = self::CONTAINER_NOT_FOUND, $extra = null, Throwable $previous = null)
    {
        parent::__construct($message, $code, $extra, $previous);
    }
}
