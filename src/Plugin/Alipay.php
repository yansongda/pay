<?php

namespace Yansongda\Pay\Plugin;

use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Pay;

class Alipay implements PluginInterface
{
    const URL = [
        Pay::MODE_NORMAL => 'https://openapi.alipay.com/gateway.do',
        Pay::MODE_SANDBOX => 'https://openapi.alipaydev.com/gateway.do',
        Pay::MODE_SERVICE => 'https://openapi.alipay.com/gateway.do',
    ];
}
