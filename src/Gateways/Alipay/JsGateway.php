<?php

namespace Yansongda\Pay\Gateways\Alipay;



use Yansongda\Supports\Collection;

class JsGateway extends MiniGateway
{
    public function pay($endpoint, array $payload): Collection
    {
        $pay_request['tradeNO'] = parent::pay($endpoint, $payload)->get('trade_no');
        return new Collection($pay_request);
    }
}
