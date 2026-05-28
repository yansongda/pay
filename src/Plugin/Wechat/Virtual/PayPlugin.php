<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/virtual-payment.html#_2-2-%E5%AE%A2%E6%88%B7%E7%AB%AF-API
 */
class PayPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付代币充值，参数为空');
        }

        $env = (int) $payload->get('env', 0);

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => $payload->get('_url', '/xpay/query_user_balance'),
                '_env' => $env,
                'offerId' => $config->getVirtualPay()->getOfferId() ?? '',
                'buyQuantity' => $payload->get('buyQuantity'),
                'env' => $env,
                'currencyType' => $payload->get('currencyType', 'CNY'),
                'productId' => $payload->get('productId', ''),
                'goodsPrice' => $payload->get('goodsPrice'),
                'outTradeNo' => $payload->get('outTradeNo', ''),
                'attach' => $payload->get('attach', ''),
            ],
            self::getWechatVirtualAccessToken($payload),
        ));

        Logger::info('[Wechat][Virtual][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
