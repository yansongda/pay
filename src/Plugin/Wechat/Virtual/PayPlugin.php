<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * 虚拟支付代币充值插件.
 *
 * 此插件用于准备签名数据，支持两种场景：
 * 1. 客户端签名（通过 virtual() 方法调用）：uri 固定为 requestVirtualPayment
 * 2. 服务端 API（通过 pay() 方法调用）：uri 为具体 API 路径如 /xpay/query_user_balance
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/virtual-payment.html#_2-2-%E5%AE%A2%E6%88%B7%E7%AB%AF-API
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/virtual-payment.html#_2-5-%E7%AD%BE%E5%90%8D%E8%AF%A6%E8%A7%A3
 */
class PayPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
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

        $offerId = $config->getVirtualPay()->getOfferId();

        if (empty($offerId)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信虚拟支付配置 -- [virtual_pay.offer_id]');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            // 客户端签名场景使用 requestVirtualPayment，服务端 API 场景使用具体路径
            '_url' => $payload->get('_url', 'requestVirtualPayment'),
            'offerId' => $offerId,
            'currencyType' => $payload->get('currencyType', 'CNY'),
        ]);

        // 客户端签名场景：不发送 HTTP 请求，返回签名数据给前端
        $rocket->setDirection(NoHttpRequestDirection::class);

        Logger::info('[Wechat][Virtual][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        /** @var Rocket $rocket */
        $rocket = $next($rocket);
        $rocket->setDestination($rocket->getPayload());

        return $rocket;
    }
}
