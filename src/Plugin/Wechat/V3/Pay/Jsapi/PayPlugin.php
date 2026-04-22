<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi;

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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/jsapi-payment/direct-jsons/jsapi-prepay.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-jsapi-payment/partner-jsons/partner-jsapi-prepay.html
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
        Logger::debug('[Wechat][V3][Pay][Jsapi][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();
        $config = self::getProviderConfig('wechat', $params);

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Jsapi 下单，参数为空');
        }

        if (Pay::MODE_SERVICE === $config->getMode()) {
            $data = $this->service($payload, $config, $params);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/pay/transactions/jsapi',
                '_service_url' => 'v3/pay/partner/transactions/jsapi',
                'notify_url' => $payload->get('notify_url', $config->getNotifyUrl()),
            ],
            $data ?? $this->normal($config, $params)
        ));

        Logger::info('[Wechat][V3][Pay][Jsapi][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(WechatConfig $config, array $params): array
    {
        return [
            'appid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
            'mchid' => $config->getMchId(),
        ];
    }

    protected function service(Collection $payload, WechatConfig $config, array $params): array
    {
        $data = [
            'sp_appid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
            'sp_mchid' => $config->getMchId(),
            'sub_mchid' => $payload->get('sub_mchid', $config->getSubMchId() ?? ''),
        ];

        if ($payload->has('payer.sub_openid')) {
            $data['sub_appid'] = $config->getSubAppIdByType($params['_type'] ?? 'mp') ?? '';
        }

        return $data;
    }
}
