<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Transfer;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter4_3_3.shtml
 */
class QueryBatchDetailIdPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'GET';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setPayload(null);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (!$payload->has('batch_id') || !$payload->get('detail_id')) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        }

        return 'v3/transfer/batches/batch-id/'.
            $payload->get('batch_id').
            '/details/detail-id/'.
            $payload->get('detail_id');
    }

    /**
     * @throws InvalidParamsException
     */
    protected function getPartnerUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (!$payload->has('batch_id') || !$payload->has('detail_id')) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        }

        return 'v3/partner-transfer/batches/batch-id/'.
            $payload->get('batch_id').
            '/details/detail-id/'.
            $payload->get('detail_id');
    }
}
