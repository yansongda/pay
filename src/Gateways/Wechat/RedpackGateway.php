<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Symfony\Component\HttpFoundation\Request;
use Yansongda\Pay\Gateways\Wechat;
use Yansongda\Pay\Log;
use Yansongda\Supports\Collection;

class RedpackGateway extends Gateway
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
        php_sapi_name() === 'cli' ?: $payload['client_ip'] = Request::createFromGlobals()->server->get('SERVER_ADDR');

        $this->mode !== Wechat::MODE_SERVICE ?: $payload['msgappid'] = $payload['appid'];

        unset($payload['appid'], $payload['trade_type'], $payload['notify_url'], $payload['spbill_create_ip']);

        $payload['sign'] = Support::generateSign($payload, $this->config->get('key'));

        Log::debug('Paying A Redpack Order:', [$endpoint, $payload]);

        return Support::requestApi(
            'mmpaymkttransfers/sendredpack',
            $payload,
            $this->config->get('key'),
            ['cert' => $this->config->get('cert_client'), 'ssl_key' => $this->config->get('cert_key')]
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
