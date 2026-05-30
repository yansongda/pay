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
        $orderId = $payload->get('order_id');
        $wxOrderId = $payload->get('wx_order_id');
        $refundOrderId = $payload->get('refund_order_id');
        $leftFee = $payload->get('left_fee');
        $refundFee = $payload->get('refund_fee');
        $bizMeta = $payload->get('biz_meta');
        $refundReason = $payload->get('refund_reason');
        $reqFrom = $payload->get('req_from');

        if (empty($openid) || empty($refundOrderId) || !isset($leftFee) || !isset($refundFee) || !isset($refundReason) || !isset($reqFrom)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付退款，缺少必要参数');
        }

        if (empty($orderId) && empty($wxOrderId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付退款，需要 order_id 或 wx_order_id');
        }

        $data = [
            '_method' => 'POST',
            '_url' => '/xpay/refund_order',
            'openid' => $openid,
            'refund_order_id' => $refundOrderId,
            'left_fee' => $leftFee,
            'refund_fee' => $refundFee,
            'biz_meta' => $bizMeta,
            'refund_reason' => $refundReason,
            'req_from' => $reqFrom,
        ];

        if (!empty($orderId)) {
            $data['order_id'] = $orderId;
        }

        if (!empty($wxOrderId)) {
            $data['wx_order_id'] = $wxOrderId;
        }

        $rocket->mergePayload($data);

        Logger::info('[Wechat][Virtual][Order][RefundOrderPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
