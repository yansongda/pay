<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2\Pay\Mini;

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
 * @see https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=7_7&index=5
 */
class InvokePlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws Exception
     * @throws InvalidResponseException
     * @throws ServiceNotFoundException
     * @throws Throwable                生成随机串失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Wechat][V2][Pay][Mini][InvokePlugin] 插件开始装载', ['rocket' => $rocket]);

        $destination = $rocket->getDestination();
        $prepayId = $destination?->get('prepay_id') ?? null;

        if (is_null($prepayId)) {
            Logger::error('[Wechat][V2][Pay][Mini][InvokePlugin] 预下单失败：响应缺少 `prepay_id` 参数，请自行检查参数是否符合微信要求', $destination ? $destination->all() : null);

            throw new InvalidResponseException(Exception::RESPONSE_MISSING_NECESSARY_PARAMS, $destination ? $destination->get('message') : '预下单失败：响应缺少 `prepay_id` 参数，请自行检查参数是否符合微信要求', $destination ? $destination->all() : null);
        }

        $params = $rocket->getParams();
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();

        $rocket->setDestination($this->getInvokeConfig($payload, $config, $prepayId));

        Logger::info('[Wechat][V2][Pay][Mini][InvokePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws InvalidConfigException
     * @throws Throwable              生成随机串失败
     */
    protected function getInvokeConfig(?Collection $payload, WechatConfig $config, string $prepayId): Config
    {
        $invokeConfig = new Config([
            'appId' => $this->getAppId($payload, $config),
            'timeStamp' => time().'',
            'nonceStr' => Str::random(32),
            'package' => 'prepay_id='.$prepayId,
            'signType' => 'MD5',
        ]);

        $invokeConfig->set('paySign', self::getWechatSignV2($config, $invokeConfig->all()));

        return $invokeConfig;
    }

    protected function getAppId(?Collection $payload, WechatConfig $config): string
    {
        if (Pay::MODE_SERVICE === $config->getMode()) {
            return $payload?->get('_invoke_appid') ?? ($config->getSubMiniAppId() ?? '');
        }

        return $payload?->get('_invoke_appid') ?? ($config->getMiniAppId() ?? '');
    }
}
