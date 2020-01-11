<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Symfony\Component\HttpFoundation\Request;
use Yansongda\Pay\Events;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Gateways\Wechat;
use Yansongda\Supports\Collection;

class TransferGateway extends Gateway
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
        if (Wechat::MODE_SERVICE === $this->mode) {
            unset($payload['sub_mch_id'], $payload['sub_appid']);
        }

        $type = Support::getTypeName($payload['type'] ?? '');

        $payload['mch_appid'] = Support::getInstance()->getConfig($type, '');
        $payload['mchid'] = $payload['mch_id'];

        if ('cli' !== php_sapi_name() && !isset($payload['spbill_create_ip'])) {
            $payload['spbill_create_ip'] = Request::createFromGlobals()->server->get('SERVER_ADDR');
        }

        unset($payload['appid'], $payload['mch_id'], $payload['trade_type'],
            $payload['notify_url'], $payload['type']);

        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Wechat', 'Transfer', $endpoint, $payload));

        return Support::requestApi(
            'mmpaymkttransfers/promotion/transfers',
            $payload,
            true
        );
    }

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
            'endpoint' => 'mmpaymkttransfers/gettransferinfo',
            'order' => is_array($order) ? $order : ['partner_trade_no' => $order],
            'cert' => true,
        ];
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
