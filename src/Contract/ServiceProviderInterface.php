<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

interface ServiceProviderInterface
{
    /**
     * @param mixed $data
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public function register($data = null): void;
}
