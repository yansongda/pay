<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Risk\Complaints;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter10_2_19.shtml
 */
class UpdateRefundPlugin extends GeneralPlugin
{
    protected function doSomething(Rocket $rocket): void
    {
        $rocket->getPayload()->forget('complaint_id');
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('complaint_id'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/merchant-service/complaints-v2/'.
            $payload->get('complaint_id').
            '/update-refund-progress';
    }
}
