<?php

namespace Yansongda\Pay\Plugin;

use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Pay;

class Wechat implements PluginInterface
{
    const URL = [
        Pay::MODE_NORMAL => 'https://api.mch.weixin.qq.com/',
        Pay::MODE_SANDBOX => 'https://api.mch.weixin.qq.com/sandboxnew/',
        Pay::MODE_SERVICE => 'https://api.mch.weixin.qq.com/',
    ];
}
