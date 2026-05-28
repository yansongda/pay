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
 * @see https://developers.weixin.qq.com/miniprogram/dev/server/API/VirtualPayment/api_query_order
 */
class QueryOrderPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][Order][QueryOrderPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付查询订单，参数为空');
        }

        $openid = $payload->get('openid');
        $env = $payload->get('env');
        $orderId = $payload->get('order_id');
        $wxOrderId = $payload->get('wx_order_id');

        if (empty($openid) || !isset($env)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付查询订单，缺少 openid 或 env');
        }

        if (empty($orderId) && empty($wxOrderId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付查询订单，需要 order_id 或 wx_order_id');
        }

        $data = [
            '_method' => 'POST',
            '_url' => '/xpay/query_order',
            'openid' => $openid,
        ];

        if (!empty($orderId)) {
            $data['order_id'] = $orderId;
        }

        if (!empty($wxOrderId)) {
            $data['wx_order_id'] = $wxOrderId;
        }

        $rocket->mergePayload($data);

        Logger::info('[Wechat][Virtual][Order][QueryOrderPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
