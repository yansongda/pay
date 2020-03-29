<?php

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;

class WechatServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $data): void
    {
        // TODO: Implement prepare() method.
    }

    /**
     * {@inheritdoc}
     */
    public function register(Pay $pay): void
    {
        // TODO: Implement register() method.
    }
}
