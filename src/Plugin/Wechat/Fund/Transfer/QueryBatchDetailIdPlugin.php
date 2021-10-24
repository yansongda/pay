<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Transfer;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

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
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('batch_id')) || is_null($payload->get('detail_id'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/transfer/batches/batch-id/'.
            $payload->get('batch_id').
            '/details/detail-id/'.
            $payload->get('detail_id');
    }

    protected function getPartnerUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('batch_id')) || is_null($payload->get('detail_id'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/partner-transfer/batches/batch-id/'.
            $payload->get('batch_id').
            '/details/detail-id/'.
            $payload->get('detail_id');
    }
}
