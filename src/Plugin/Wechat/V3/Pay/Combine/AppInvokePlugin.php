<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine;

use Closure;
use Throwable;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/orders/app-transfer-payment.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/combine-payment/orders/app-transfer-payment.html
 */
class AppInvokePlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidResponseException
     * @throws ServiceNotFoundException
     * @throws Throwable                生成随机串失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Wechat][Pay][Combine][AppInvokePlugin] 插件开始装载', ['rocket' => $rocket]);

        $destination = $rocket->getDestination();
        $prepayId = $destination?->get('prepay_id');

        if (is_null($prepayId)) {
            Logger::error('[Wechat][Pay][Combine][AppInvokePlugin] 预下单失败：响应缺少 `prepay_id` 参数，请自行检查参数是否符合微信要求', $destination?->all() ?? null);

            throw new InvalidResponseException(Exception::RESPONSE_MISSING_NECESSARY_PARAMS, $destination?->get('message') ?? '预下单失败：响应缺少 `prepay_id` 参数，请自行检查参数是否符合微信要求', $destination?->all() ?? null);
        }

        $params = $rocket->getParams();
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();

        $rocket->setDestination($this->getInvokeConfig($payload, $config, $prepayId));

        Logger::info('[Wechat][Pay][Combine][AppInvokePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws InvalidConfigException
     * @throws Throwable              生成随机串失败
     */
    protected function getInvokeConfig(?Collection $payload, WechatConfig $config, string $prepayId): Config
    {
        $invokeConfig = new Config([
            'appid' => $this->getAppId($payload, $config),
            'partnerid' => $this->getPartnerId($payload, $config),
            'prepayid' => $prepayId,
            'package' => 'Sign=WXPay',
            'noncestr' => Str::random(32),
            'timestamp' => time().'',
        ]);

        $invokeConfig->set('sign', $this->getSign($invokeConfig, $config));

        return $invokeConfig;
    }

    /**
     * @throws InvalidConfigException
     */
    protected function getSign(Collection $invokeConfig, WechatConfig $config): string
    {
        $contents = $invokeConfig->get('appid', '')."\n"
            .$invokeConfig->get('timestamp', '')."\n"
            .$invokeConfig->get('noncestr', '')."\n"
            .$invokeConfig->get('prepayid', '')."\n";

        return self::getWechatSign($config, $contents);
    }

    protected function getAppId(?Collection $payload, WechatConfig $config): string
    {
        if (Pay::MODE_SERVICE === $config->getMode()) {
            return $payload?->get('_invoke_appid') ?? ($config->getSubAppId() ?? '');
        }

        return $payload?->get('_invoke_appid') ?? ($config->getAppId() ?? '');
    }

    protected function getPartnerId(?Collection $payload, WechatConfig $config): string
    {
        return $payload?->get('_invoke_partnerid') ?? $config->getMchId();
    }
}
