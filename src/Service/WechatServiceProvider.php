<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat;

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
        $pay::set(Wechat::class, '');
        $pay::set('wechat', '');
        $pay::set('wechat.http', '');
    }
}
