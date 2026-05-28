<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual\Currency;

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
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/virtual-payment.html
 */
class CurrencyPayPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][Currency][CurrencyPayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付代币扣减，参数为空');
        }

        $env = (int) $payload->get('env', 0);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/xpay/currency_pay',
            '_env' => $env,
            'openid' => $payload->get('openid', ''),
            'env' => $env,
            'user_ip' => $payload->get('user_ip', ''),
            'amount' => $payload->get('amount'),
            'order_id' => $payload->get('order_id', ''),
            'payitem' => $payload->get('payitem', ''),
            'remark' => $payload->get('remark', ''),
        ]);

        Logger::info('[Wechat][Virtual][Currency][CurrencyPayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
