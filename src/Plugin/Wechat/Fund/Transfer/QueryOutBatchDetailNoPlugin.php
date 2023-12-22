<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Transfer;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter4_3_6.shtml
 */
class QueryOutBatchDetailNoPlugin extends GeneralPlugin
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

        if (!$payload->has('out_batch_no') || !$payload->has('out_detail_no')) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        }

        return 'v3/transfer/batches/out-batch-no/'.
            $payload->get('out_batch_no').
            '/details/out-detail-no/'.
            $payload->get('out_detail_no');
    }

    /**
     * @throws InvalidParamsException
     */
    protected function getPartnerUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (!$payload->has('out_batch_no') || !$payload->has('out_detail_no')) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        }

        return 'v3/partner-transfer/batches/out-batch-no/'.
            $payload->get('out_batch_no').
            '/details/out-detail-no/'.
            $payload->get('out_detail_no');
    }
}
