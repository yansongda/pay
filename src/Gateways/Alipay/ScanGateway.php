<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Events;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Gateways\Alipay;
use Yansongda\Supports\Collection;

class ScanGateway extends Gateway
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     * @param array $payload
     *
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     *
     * @throws InvalidArgumentException
     * @return Collection
     */
    public function pay($endpoint, array $payload): Collection
    {
        $payload['method'] = 'alipay.trade.precreate';

        $biz_array = json_decode($payload['biz_content'], true);
        if (($this->mode === Alipay::MODE_SERVICE) && (!empty(Support::getInstance()->pid))) {
            //服务商模式且服务商pid参数不为空
            $biz_array['extend_params'] = is_array($biz_array['extend_params']) ? array_merge(['sys_service_provider_id' => Support::getInstance()->pid], $biz_array['extend_params']) : ['sys_service_provider_id' => Support::getInstance()->pid];
        }
        $payload['biz_content'] = json_encode(array_merge($biz_array,
            ['product_code' => '']
        ));
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Alipay', 'Scan', $endpoint, $payload));

        return Support::requestApi($payload);
    }
}
