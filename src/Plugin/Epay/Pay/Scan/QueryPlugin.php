<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay\Pay\Scan;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Epay\GeneralPlugin;

class QueryPlugin extends GeneralPlugin
{
    protected function getService(): string
    {
        return 'payCheck';
    }

    protected function doSomethingBefore(Rocket $rocket): void
    {
        $rocket->mergePayload([
            'deviceNo' => '1234567890',
        ]);
    }
}
