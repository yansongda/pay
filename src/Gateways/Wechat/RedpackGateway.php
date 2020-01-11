<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Symfony\Component\HttpFoundation\Request;
use Yansongda\Pay\Events;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Gateways\Wechat;
use Yansongda\Supports\Collection;

class RedpackGateway extends Gateway
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     */
    public function pay($endpoint, array $payload): Collection
    {
        $payload['wxappid'] = $payload['appid'];

        if ('cli' !== php_sapi_name()) {
            $payload['client_ip'] = Request::createFromGlobals()->server->get('SERVER_ADDR');
        }

        if (Wechat::MODE_SERVICE === $this->mode) {
            $payload['msgappid'] = $payload['appid'];
        }

        unset($payload['appid'], $payload['trade_type'],
              $payload['notify_url'], $payload['spbill_create_ip']);

        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Wechat', 'Redpack', $endpoint, $payload));

        return Support::requestApi(
            'mmpaymkttransfers/sendredpack',
            $payload,
            true
        );
    }

    /**
     * Get trade type config.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function getTradeType(): string
    {
        return '';
    }
}
