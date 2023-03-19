<?php

namespace Yansongda\Pay\Tests\Stubs\Plugin;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class WechatGeneralPluginStub extends GeneralPlugin
{
    protected function doSomething(Rocket $rocket): void
    {
        $rocket->mergePayload(['config_key' => $this->getConfigKey($rocket->getParams())]);
    }

    protected function getUri(Rocket $rocket): string
    {
        return 'yansongda/pay';
    }

    protected function getPartnerUri(Rocket $rocket): string
    {
        return 'yansongda/pay/partner';
    }
}
