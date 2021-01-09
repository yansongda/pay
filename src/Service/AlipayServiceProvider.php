<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay;

class AlipayServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $data): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function register(Pay $pay): void
    {
        $service = new Alipay();

        $pay::set(Alipay::class, $service);
        $pay::set('alipay', $service);
    }
}
