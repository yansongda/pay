<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Service\AbstractServiceProvider;
use Yansongda\Pay\Provider\Wechat;

class WechatServiceProvider extends AbstractServiceProvider
{
    protected function getProviderClass(): string
    {
        return Wechat::class;
    }

    protected function getProviderName(): string
    {
        return 'wechat';
    }
}
