<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe;

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
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/vip.html
 */
class SendSubscribePrePaymentPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][Subscribe][SendSubscribePrePaymentPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付预通知扣款，参数为空');
        }

        $env = (int) $payload->get('env', 0);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/xpay/send_subscribe_pre_payment',
            '_env' => $env,
            'openid' => $payload->get('openid'),
            'env' => $env,
            'contract_id' => $payload->get('contract_id'),
            'pre_payment_amount' => $payload->get('pre_payment_amount'),
        ]);

        Logger::info('[Wechat][Virtual][Subscribe][SendSubscribePrePaymentPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
