<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2;

use JetBrains\PhpStorm\Deprecated;
use Yansongda\Pay\Plugin\Alipay\CallbackPlugin as BaseCallbackPlugin;

#[Deprecated(reason: '自 v3.7.21 开始废弃', replacement: 'Yansongda\\Pay\\Plugin\\Alipay\\CallbackPlugin')]
class CallbackPlugin extends BaseCallbackPlugin
{
}
