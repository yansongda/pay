<?php

namespace Yansongda\Pay\Tests\Stubs\Plugin;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

class AlipayGeneralPluginStub extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'yansongda';
    }
}
