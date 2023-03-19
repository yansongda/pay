<?php

namespace Yansongda\Pay\Tests\Stubs\Plugin;

use Yansongda\Pay\Plugin\Wechat\GeneralV2Plugin;
use Yansongda\Pay\Rocket;

class WechatGeneralV2PluginStub extends GeneralV2Plugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'yansongda/pay';
    }
}
