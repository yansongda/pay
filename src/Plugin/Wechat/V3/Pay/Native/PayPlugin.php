<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\Native;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/native-payment/direct-jsons/native-prepay.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-native-payment/partner-jsons/partner-native-prepay.html
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
        Logger::debug('[Wechat][V3][Pay][Native][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();
        $config = self::getProviderConfig('wechat', $params);

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Native 下单，参数为空');
        }

        if (Pay::MODE_SERVICE === $config->getMode()) {
            $data = $this->service($payload, $params, $config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/pay/transactions/native',
                '_service_url' => 'v3/pay/partner/transactions/native',
                'notify_url' => $payload->get('notify_url', $config->getNotifyUrl()),
            ],
            $data ?? $this->normal($params, $config)
        ));

        Logger::info('[Wechat][V3][Pay][Native][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(array $params, WechatConfig $config): array
    {
        return [
            'appid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
            'mchid' => $config->getMchId(),
        ];
    }

    protected function service(Collection $payload, array $params, WechatConfig $config): array
    {
        return [
            'sp_appid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
            'sp_mchid' => $config->getMchId(),
            'sub_mchid' => $payload->get('sub_mchid', $config->getSubMchId() ?? ''),
        ];
    }
}
