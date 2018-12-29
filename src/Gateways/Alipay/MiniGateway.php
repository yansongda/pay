<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Events;
use Yansongda\Supports\Collection;

class MiniGateway implements GatewayInterface
{
    /**
     * Pay an order.
     *
     * @author xiaozan <i@xiaozan.me>
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     *
     * @link https://docs.alipay.com/mini/introduce/pay
     *
     * @return Collection
     */
    public function pay($endpoint, array $payload): Collection
    {
        if (empty(json_decode($payload['biz_content'], true)['buyer_id'])) {
            throw new \Yansongda\Pay\Exceptions\InvalidArgumentException('buyer_id required');
        }

        $payload['method'] = $this->getMethod();
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(Events::PAY_STARTED, new Events\PayStarted('Alipay', 'Mini', $endpoint, $payload));

        return Support::requestApi($payload);
    }

    /**
     * Get method config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return 'alipay.trade.create';
    }
}
