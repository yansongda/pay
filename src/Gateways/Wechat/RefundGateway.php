<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\InvalidArgumentException;

class RefundGateway extends Gateway
{
    /**
     * Find.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param $order
     */
    public function find($order): array
    {
        return [
            'endpoint' => 'pay/refundquery',
            'order' => is_array($order) ? $order : ['out_trade_no' => $order],
            'cert' => false,
        ];
    }

    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     *
     * @throws InvalidArgumentException
     */
    public function pay($endpoint, array $payload)
    {
        throw new InvalidArgumentException('Not Support Refund In Pay');
    }

    /**
     * Get trade type config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws InvalidArgumentException
     */
    protected function getTradeType()
    {
        throw new InvalidArgumentException('Not Support Refund In Pay');
    }
}
