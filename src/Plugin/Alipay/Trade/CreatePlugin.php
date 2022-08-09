<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

/**
 * @see https://opendocs.alipay.com/open/02ekfj
 */
class CreatePlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.trade.create';
    }
}
