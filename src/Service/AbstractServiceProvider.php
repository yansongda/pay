<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Artful\Contract\ServiceProviderInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Pay\Pay;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws ContainerException
     */
    public function register(mixed $data = null): void
    {
        $service = $this->makeService();

        Pay::set($service::class, $service);
        Pay::set($this->getProviderName(), $service);
    }

    abstract protected function makeService(): ProviderInterface;

    abstract protected function getProviderName(): string;
}
