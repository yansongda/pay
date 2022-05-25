<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Risk\Complaints;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter10_2_4.shtml
 */
class UpdateCallbackPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'PUT';
    }

    protected function doSomething(Rocket $rocket): void
    {
    }

    protected function getUri(Rocket $rocket): string
    {
        return 'v3/merchant-service/complaint-notifications';
    }
}
