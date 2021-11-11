<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class ReturnAdvancelugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('refund_id'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/ecommerce/refunds/'.$payload->get('refund_id').'/return-advance';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());

        $body = [
            'sub_mchid' => $rocket->getPayload()->get('sub_mchid', $config->get('sub_mch_id', '')),
        ];

        $rocket->setPayload(new Collection($body));
    }
}
