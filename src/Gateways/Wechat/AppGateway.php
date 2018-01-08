<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Log;
use Yansongda\Supports\Str;

class AppGateway extends Gateway
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @return Response
     */
    public function pay($endpoint, array $payload): Response
    {
        $payload['appid'] = $this->config->get('appid');
        $payload['trade_type'] = $this->getTradeType();

        $payRequest = [
            'appid'     => $payload['appid'],
            'partnerid' => $payload['mch_id'],
            'prepayid'  => $this->preOrder('pay/unifiedorder', $payload)->prepay_id,
            'timestamp' => strval(time()),
            'noncestr'  => Str::random(),
            'package'   => 'Sign=WXPay',
        ];
        $payRequest['sign'] = Support::generateSign($payRequest, $this->config->get('key'));

        Log::debug('Paying An App Order:', [$endpoint, $payRequest]);

        return JsonResponse::create($payRequest);
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
        return 'APP';
    }
}
