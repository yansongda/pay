<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Coupon;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class CreatePlugin extends GeneralPlugin
{
    protected function doSomething(Rocket $rocket): void
    {
    }

    protected function getUri(Rocket $rocket): string
    {
        return 'v3/marketing/favor/coupon-stocks';
    }
}
