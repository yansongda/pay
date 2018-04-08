<?php

namespace Hanwenbo\Pay\Gateways\Wechat;

use Hanwenbo\Pay\Gateways\Wechat;
use Hanwenbo\Supports\Collection;

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
    public function pay($endpoint, array $payload): Collection
    {
        $payload['appid'] = $this->config->get('miniapp_id');

        $this->mode !== Wechat::MODE_SERVICE ?: $payload['sub_appid'] = $this->config->get('sub_miniapp_id');

        return parent::pay($endpoint, $payload);
    }
}
