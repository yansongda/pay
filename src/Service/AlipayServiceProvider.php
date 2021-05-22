<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay;

class AlipayServiceProvider implements ServiceProviderInterface
{
    public function register(Pay $pay, ?array $data = null): void
    {
        $service = function () {
            return new Alipay();
        };

        $pay::set(Alipay::class, $service);
        $pay::set('alipay', $service);
    }
}
