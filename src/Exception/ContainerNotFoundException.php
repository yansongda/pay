<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class ContainerNotFoundException extends ContainerException
{
    public function __construct(string $message = '容器未找到', int $code = self::CONTAINER_NOT_FOUND, mixed $extra = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $extra, $previous);
    }
}
