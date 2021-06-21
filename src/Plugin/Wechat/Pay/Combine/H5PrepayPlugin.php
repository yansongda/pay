<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Combine;

use Yansongda\Pay\Plugin\Wechat\Pay\Common\CombinePrepayPlugin;
use Yansongda\Pay\Rocket;

class H5PrepayPlugin extends CombinePrepayPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/combine-transactions/h5';
    }
}
