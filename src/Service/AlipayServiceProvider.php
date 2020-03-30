<?php

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
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public function register(Pay $pay): void
    {
        $pay::set(Alipay::class, '');
        $pay::set('alipay', '');
        $pay::set('alipay.http', '');
    }
}
