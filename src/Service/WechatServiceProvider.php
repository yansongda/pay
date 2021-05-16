<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat;

class WechatServiceProvider implements ServiceProviderInterface
{
    public function register(Pay $pay, ?array $data = null): void
    {
        $pay::set(Wechat::class, '');
        $pay::set('wechat', '');
        $pay::set('wechat.http', '');
    }
}
