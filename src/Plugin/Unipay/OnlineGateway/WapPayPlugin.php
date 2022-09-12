<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\OnlineGateway;

use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Unipay\GeneralPlugin;
use Yansongda\Pay\Rocket;

/**
 * @see https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=754&apiservId=448&version=V2.2&bussType=0
 */
class WapPayPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'gateway/api/frontTransReq.do';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $payload = [
            'bizType' => '000201',
            'txnType' => '01',
            'txnSubType' => '01',
            'channelType' => '08',
        ];

        if (is_null($rocket->getPayload()) || !$rocket->getPayload()->has('currencyCode')) {
            $payload['currencyCode'] = '156';
        }

        $rocket->setDirection(ResponseParser::class)
            ->mergePayload($payload);
    }
}
