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
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/server/API/VirtualPayment/api_notify_provide_goods
 */
class NotifyProvideGoodsPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][Order][NotifyProvideGoodsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        $orderId = $payload->get('order_id');
        $wxOrderId = $payload->get('wx_order_id');

        if (empty($orderId) && empty($wxOrderId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付通知发货，需要 order_id 或 wx_order_id');
        }

        $data = [
            '_method' => 'POST',
            '_url' => '/xpay/notify_provide_goods',
        ];

        if (!empty($orderId)) {
            $data['order_id'] = $orderId;
        }

        if (!empty($wxOrderId)) {
            $data['wx_order_id'] = $wxOrderId;
        }

        $rocket->mergePayload($data);

        Logger::info('[Wechat][Virtual][Order][NotifyProvideGoodsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
