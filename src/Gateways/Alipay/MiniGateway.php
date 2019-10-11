<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Events;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Gateways\Alipay;
use Yansongda\Supports\Collection;

class MiniGateway extends Gateway
{
    /**
     * Pay an order.
     *
     * @author xiaozan <i@xiaozan.me>
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     *
     * @link https://docs.alipay.com/mini/introduce/pay
     *
     * @return Collection
     */
    public function pay($endpoint, array $payload): Collection
    {
        $biz_array=json_decode($payload['biz_content'], true);
        if (empty($biz_array['buyer_id'])) {
            throw new InvalidArgumentException('buyer_id required');
        }
        if (($this->mode === Alipay::MODE_SERVICE) && (!empty(Support::getInstance()->pid))) {
            //服务商模式且服务商pid参数不为空
            $biz_array['extend_params'] = is_array($biz_array['extend_params']) ? array_merge(['sys_service_provider_id' => Support::getInstance()->pid], $biz_array['extend_params']) : ['sys_service_provider_id' => Support::getInstance()->pid];
        }
        $payload['method'] = 'alipay.trade.create';
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Alipay', 'Mini', $endpoint, $payload));

        return Support::requestApi($payload);
    }
}
