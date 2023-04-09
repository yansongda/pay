<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Unipay;

class UnipayServiceProvider implements ServiceProviderInterface
{
    /**
     * @param mixed $data
     *
     * @throws ContainerException
     */
    public function register($data = null): void
    {
        $service = new Unipay();

        Pay::set(Unipay::class, $service);
        Pay::set('unipay', $service);
    }
}
