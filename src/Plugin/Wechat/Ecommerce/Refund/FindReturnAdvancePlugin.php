<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class FindReturnAdvancePlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        throw new InvalidParamsException(Exception::SERVICE_NOT_FOUND_ERROR);
    }

    protected function getPartnerUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('refund_id'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        $config = get_wechat_config($rocket->getParams());

        $query = [
            'sub_mchid' => $payload->get('sub_mchid', $config->get('sub_mch_id', '')),
        ];

        return 'v3/ecommerce/refunds/'.$payload->get('refund_id').'/return-advance?'.http_build_query($query);
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
