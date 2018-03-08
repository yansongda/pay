<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Gateways\Wechat;
use Yansongda\Supports\Collection;

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
