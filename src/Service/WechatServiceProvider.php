<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Wechat;

class WechatServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public function register(Pay $pay, ?array $data = null): void
    {
        $service = function () {
            return new Wechat();
        };

        $pay::set(Wechat::class, $service);
        $pay::set('wechat', $service);
    }
}
