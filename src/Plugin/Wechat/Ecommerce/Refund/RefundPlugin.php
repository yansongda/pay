<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class RefundPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        throw new InvalidParamsException(Exception::SERVICE_NOT_FOUND_ERROR);
    }

    protected function getPartnerUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('transaction_id')) && is_null($payload->get('out_trade_no'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        } elseif (is_null($payload->get('out_refund_no'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }
        $amount = $payload->get('amount');
        if (is_null($amount) || !isset($amount['refund']) || !isset($amount['total']) || !isset($amount['currency'])) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/ecommerce/refunds/apply';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());

        $wechatId = [
            'sub_mchid' => $rocket->getPayload()->get('sub_mchid', $config->get('sub_mch_id', '')),
            'sp_appid' => $config->get('mini_app_id', $config->get('mp_app_id', '')),
        ];

        if (!$rocket->getPayload()->has('notify_url')) {
            $wechatId['notify_url'] = $config->get('notify_url');
        }

        $rocket->mergePayload($wechatId);
    }
}
