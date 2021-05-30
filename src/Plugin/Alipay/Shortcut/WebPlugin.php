<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Shortcut;

use Closure;
use Yansongda\Pay\Plugin\Alipay\Trade\PagePayPlugin;
use Yansongda\Pay\Rocket;

class WebPlugin extends PagePayPlugin
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        return parent::assembly(...func_get_args());
    }
}
