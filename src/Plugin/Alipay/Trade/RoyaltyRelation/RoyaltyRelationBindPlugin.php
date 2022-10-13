<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade\RoyaltyRelation;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

/**
 * @see https://opendocs.alipay.com/open/02c7hq?ref=api
 */
class RoyaltyRelationBindPlugin extends GeneralPlugin
{
    public function getMethod(): string
    {
        return 'alipay.trade.royalty.relation.bind';
    }
}
