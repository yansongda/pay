<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual\Order;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/server/API/VirtualPayment/api_notify_provide_goods
 */
class NotifyProvideGoodsPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][Order][NotifyProvideGoodsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'xpay/notify_provide_goods',
        ]);

        Logger::info('[Wechat][Virtual][Order][NotifyProvideGoodsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
