<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Jsapi;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;

class PrepayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {

    }
}
