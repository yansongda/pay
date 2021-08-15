<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class QueryPlugin extends GeneralPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $config = get_wechat_config($rocket->getParams());
        $payload = $rocket->getPayload();

        //服务商模式-接口uri及参数
        if ($this->isServicePartnerMode(get_wechat_config($rocket->getParams()))) {
            $baseUriPath = 'v3/pay/partner/transactions/';
            //子商户支持配置文件定义和传参 var_dump($payload->all()); 
            $subMchid = $config->get('sub_mchid', '');
            $uriParams = [
                'sp_mchid' => $config->get('mch_id', ''),
                'sub_mchid' => !empty($subMchid) ? $subMchid : $payload->get('sub_mchid', '')
            ];
        } else {
            $baseUriPath = 'v3/pay/transactions/';
            $uriParams = [
                'mchid' => $config->get('mch_id', '')
            ];
        }
        
        if (!is_null($payload->get('transaction_id'))) {
            return $baseUriPath . 'id/' . $payload->get('transaction_id') .
                   '?' . http_build_query($uriParams);
        }

        if (!is_null($payload->get('out_trade_no'))) {
            return $baseUriPath . 'out-trade-no/' . $payload->get('out_trade_no') .
                   '?' . http_build_query($uriParams);
        }

        throw new InvalidParamsException(InvalidParamsException::MISSING_NECESSARY_PARAMS);
    }

    protected function getMethod(): string
    {
        return 'GET';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setPayload(null);
    }
}
