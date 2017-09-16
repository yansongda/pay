<?php

namespace Yansongda\Pay\Gateways\Alipay;

class FundTransferGateway extends Alipay
{
    /**
     * get method config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.fund.trans.toaccount.transfer';
    }

    /**
     * get productCode config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getProductCode()
    {
        return false;
    }

    /**
     * transfer amount to account.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $config_biz
     *
     * @return array|bool
     */
    public function pay(array $config_biz = [])
    {
        return $this->getResult($config_biz, $this->getMethod());
    }
}
