<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual\Order;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/server/API/VirtualPayment/api_refund_order
 */
class RefundOrderPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][Order][RefundOrderPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付退款，参数为空');
        }

        $openid = $payload->get('openid');
        $env = $payload->get('env');
        $orderId = $payload->get('order_id');
        $outTradeNo = $payload->get('out_trade_no');
        $refundAmount = $payload->get('refund_amount');

        if (empty($openid) || !isset($env) || empty($orderId) || empty($outTradeNo) || !isset($refundAmount)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付退款，参数缺少必要参数');
        }

        $env = (int) $env;

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => '/xpay/refund_order',
                '_env' => $env,
                'openid' => $openid,
                'env' => $env,
                'order_id' => $orderId,
                'out_trade_no' => $outTradeNo,
                'refund_amount' => $refundAmount,
            ],
            $this->getAccessToken($payload),
        ));

        Logger::info('[Wechat][Virtual][Order][RefundOrderPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getAccessToken($payload): array
    {
        $token = $payload->get('_access_token', '');

        if (!empty($token)) {
            return ['_access_token' => $token];
        }

        return [];
    }
}
