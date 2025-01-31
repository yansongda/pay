<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012719578
 */
class InvokeIosPlugin implements PluginInterface
{
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

        Logger::debug('[Wechat][V3][Marketing][MchTransfer][InvokeIosPlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_provider_config('wechat', $rocket->getParams());
        $destination = $rocket->getDestination();
        $packageInfo = $destination?->get('package_info');

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE, '参数异常: iOS调起用户确认收款，只支持普通商户模式，当前配置为服务商模式');
        }

        if (is_null($packageInfo)) {
            Logger::error('[Wechat][V3][Marketing][MchTransfer][InvokeIosPlugin] iOS调起用户确认收款失败：响应缺少 `package_info` 参数，请自行检查参数是否符合微信要求', $destination?->all() ?? null);

            throw new InvalidResponseException(Exception::RESPONSE_MISSING_NECESSARY_PARAMS, $destination?->get('fail_reason') ?? 'iOS调起用户确认收款失败：响应缺少 `package_info` 参数，请自行检查参数是否符合微信要求', $destination?->all() ?? null);
        }

        $params = $rocket->getParams();
        $config = get_provider_config('wechat', $params);
        $payload = $rocket->getPayload();

        $rocket->setDestination($this->getInvokeConfig($payload, $params, $config, $packageInfo));

        Logger::info('[Wechat][V3][Marketing][MchTransfer][InvokeIosPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    protected function getInvokeConfig(?Collection $payload, array $params, array $config, string $packageInfo): Config
    {
        return new Config([
            'businessType' => 'requestMerchantTransfer',
            'query' => http_build_query([
                'appId' => $payload?->get('_invoke_appId') ?? $config[get_wechat_type_key($params)] ?? '',
                'mchId' => $payload?->get('_invoke_mchId') ?? $config['mch_id'] ?? '',
                'package' => $packageInfo,
            ]),
        ]);
    }
}
