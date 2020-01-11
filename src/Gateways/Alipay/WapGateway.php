<?php

namespace Yansongda\Pay\Gateways\Alipay;

class WapGateway extends WebGateway
{
    /**
     * Get method config.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function getMethod(): string
    {
        return 'alipay.trade.wap.pay';
    }

    /**
     * Get productCode config.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function getProductCode(): string
    {
        return 'QUICK_WAP_WAY';
    }
}
