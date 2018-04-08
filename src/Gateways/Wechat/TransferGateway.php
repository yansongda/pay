<?php

namespace Hanwenbo\Pay\Gateways\Wechat;

use Symfony\Component\HttpFoundation\Request;
use Hanwenbo\Pay\Gateways\Wechat;
use Hanwenbo\Pay\Log;
use Hanwenbo\Supports\Collection;

class TransferGateway extends Gateway
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
        if ($this->mode === Wechat::MODE_SERVICE) {
            unset($payload['sub_mch_id'], $payload['sub_appid']);
        }
        $type = isset($payload['type']) ? ($payload['type'].($payload['type'] == 'app' ?: '_').'id') : 'app_id';

        $payload['mch_appid'] = $this->config->get($type, '');
        $payload['mchid'] = $payload['mch_id'];
        php_sapi_name() === 'cli' ?: $payload['spbill_create_ip'] = Request::createFromGlobals()->server->get('SERVER_ADDR');

        unset($payload['appid'], $payload['mch_id'], $payload['trade_type'],
            $payload['notify_url'], $payload['type']);

        $payload['sign'] = Support::generateSign($payload, $this->config->get('key'));

        Log::debug('Paying A Transfer Order:', [$endpoint, $payload]);

        return Support::requestApi(
            'mmpaymkttransfers/promotion/transfers',
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
