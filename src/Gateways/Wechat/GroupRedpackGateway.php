<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Log;
use Yansongda\Supports\Collection;

class GroupRedpackGateway extends Gateway
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
        $payload['wxappid'] = $payload['appid'];
        $payload['amt_type'] = 'ALL_RAND';

        unset($payload['appid'], $payload['trade_type'], $payload['notify_url'], $payload['spbill_create_ip']);

        $payload['sign'] = Support::generateSign($payload, $this->config->get('key'));

        Log::debug('Paying A Groupredpack Order:', [$endpoint, $payload]);

        return Support::requestApi(
            'mmpaymkttransfers/sendgroupredpack',
            $payload,
            $this->config->get('key'),
            $this->config->get('cert_client'),
            $this->config->get('cert_key')
        );
    }

    /**
     * Get trade type config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getTradeType(): string
    {
        return '';
    }
}
