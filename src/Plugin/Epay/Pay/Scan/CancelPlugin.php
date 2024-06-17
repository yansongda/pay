<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay\Pay\Scan;

use Yansongda\Pay\Plugin\Epay\GeneralPlugin;

class CancelPlugin extends GeneralPlugin
{
    protected function getService(): string
    {
        return 'payCancel';
    }
}
