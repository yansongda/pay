<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class DownloadBillPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return $rocket->getParams()['download_url'] ?? '';
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
