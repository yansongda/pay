<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Jsapi;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class FindRefundPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/refund/domestic/refunds/'.($rocket->getParams()['out_refund_no'] ?? '');
    }

    protected function getMethod(): string
    {
        return 'GET';
    }

    protected function checkPayload(Rocket $rocket): void
    {
        $rocket->setPayload(null);
    }
}
