<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Yansongda\Pay\Exception\ContainerException;

interface ServiceProviderInterface
{
    /**
     * @throws ContainerException
     */
    public function register(mixed $data = null): void;
}
