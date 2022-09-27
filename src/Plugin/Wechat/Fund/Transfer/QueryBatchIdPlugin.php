<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Transfer;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter4_3_2.shtml
 */
class QueryBatchIdPlugin extends GeneralPlugin
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

        if (is_null($payload->get('batch_id'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        if ($payload->get('need_query_detail')=='true' || $payload->get('need_query_detail') == true) {
            if (is_null($payload->get('detail_status'))) {
                $payload->detail_status = 'ALL';
            } else {
                switch ($payload->get('detail_status')) {
                    case 'ALL':
                        //ALL:全部。需要同时查询转账成功和转账失败的明细单
                        break;
    
                    case 'SUCCESS':
                        //SUCCESS:转账成功。只查询转账成功的明细单
                        break;
    
                    case 'FAIL':
                        // FAIL:转账失败。只查询转账失败的明细单
                        break;
                    
                    default:
                            throw new InvalidParamsException(Exception::PARAMS_ERROR);
                        break;
                }
            }
        }else{
            $payload->need_query_detail = 'false';
        }

        $batch_id = $payload->get('batch_id');
        unset($payload->batch_id);

        return 'v3/transfer/batches/batch-id/'.$batch_id.
            '?'.$payload->query();
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getPartnerUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('batch_id')) || is_null($payload->get('need_query_detail'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/partner-transfer/batches/batch-id/'.$payload->get('batch_id').
            '?'.$payload->query();
    }
}
