<?php

namespace Yansongda\Pay\Gateways\Wechat;

class MiniappGateway extends MpGateway
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @return Collection
     */
    public function pay($endpoint, $payload): Collection
    {
        $payload['appid'] = $this->config->get('miniapp_id');

        return parent::pay($endpoint, $payload);
    }
}
