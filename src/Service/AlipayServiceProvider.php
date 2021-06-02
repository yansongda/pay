<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Packer\CollectionPacker;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;

class AlipayServiceProvider implements ServiceProviderInterface
{
    public function register(Pay $pay, ?array $data = null): void
    {
        $service = function () {
            Pay::set(
                PackerInterface::class, Pay::get(CollectionPacker::class)
            );

            return new Alipay();
        };

        $pay::set(Alipay::class, $service);
        $pay::set('alipay', $service);
    }
}
