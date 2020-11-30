<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Events;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidConfigException;

class AppGateway extends Gateway
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     *
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     */
    public function pay($endpoint, array $payload): Response
    {
        $payload['method'] = 'alipay.trade.app.pay';

        $biz_array = json_decode($payload['biz_content'], true);
        $biz_array = Support::bizArrayByMode($biz_array);
        $payload['biz_content'] = json_encode(array_merge($biz_array, ['product_code' => 'QUICK_MSECURITY_PAY']));
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Alipay', 'App', $endpoint, $payload));

        return new Response(http_build_query($payload));
    }
}
