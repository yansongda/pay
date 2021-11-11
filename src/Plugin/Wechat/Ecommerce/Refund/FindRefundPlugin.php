<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class FindRefundPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();
        
        $config = get_wechat_config($rocket->getParams());

        $query = [
            'sub_mchid' => $payload->get('sub_mchid', $config->get('sub_mch_id', '')),
        ];

        if (!is_null($payload->get('refund_id'))) {
            return 'v3/ecommerce/refunds/id/'.$payload->get('refund_id').'?'.http_build_query($query);
        } elseif (!is_null($payload->get('out_refund_no'))) {
            return 'v3/ecommerce/refunds/out-refund-no/'.$payload->get('out_refund_no').'?'.http_build_query($query);
        } else {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }
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
