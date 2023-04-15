<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Yansongda\Pay\Exception\ContainerException;

interface ServiceProviderInterface
{
    /**
     * @param mixed $data
     *
     * @throws ContainerException
     */
    public function register($data = null): void;
}
