<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
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

/**
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012716430
 */
class InvokeJsapiPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidResponseException
     * @throws ServiceNotFoundException
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Wechat][V3][Marketing][Transfer][InvokeJsapiPlugin] 插件开始装载', ['rocket' => $rocket]);

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $rocket->getParams());
        $destination = $rocket->getDestination();
        $packageInfo = $destination?->get('package_info');

        if (Pay::MODE_SERVICE === $config->getMode()) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE, '参数异常: JSAPI调起用户确认收款，只支持普通商户模式，当前配置为服务商模式');
        }

        if (is_null($packageInfo)) {
            Logger::error('[Wechat][V3][Marketing][Transfer][InvokeJsapiPlugin] JSAPI调起用户确认收款失败：响应缺少 `package_info` 参数，请自行检查参数是否符合微信要求', $destination?->all() ?? null);

            throw new InvalidResponseException(Exception::RESPONSE_MISSING_NECESSARY_PARAMS, $destination?->get('fail_reason') ?? 'JSAPI调起用户确认收款失败：响应缺少 `package_info` 参数，请自行检查参数是否符合微信要求', $destination?->all() ?? null);
        }

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        $rocket->setDestination($this->getInvokeConfig($payload, $params, $config, $packageInfo));

        Logger::info('[Wechat][V3][Marketing][Transfer][InvokeJsapiPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    protected function getInvokeConfig(?Collection $payload, array $params, WechatConfig $config, string $packageInfo): Config
    {
        return new Config([
            'appId' => $payload?->get('_invoke_appId') ?? ($config->getAppIdByType($params['_type'] ?? 'mp') ?? ''),
            'mchId' => $payload?->get('_invoke_mchId') ?? $config->getMchId(),
            'package' => $packageInfo,
        ]);
    }
}
