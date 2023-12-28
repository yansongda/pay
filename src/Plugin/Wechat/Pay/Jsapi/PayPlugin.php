<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Jsapi;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/jsapi-payment/direct-jsons/jsapi-prepay.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-jsapi-payment/partner-jsons/partner-jsapi-prepay.html
 */
class PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Jsapi][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();
        $config = get_wechat_config($params);

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Jsapi 下单，参数为空');
        }

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            $data = $this->service($payload, $config, $params);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/pay/transactions/jsapi',
                '_service_url' => 'v3/pay/partner/transactions/jsapi',
                'notify_url' => $payload->get('notify_url', $config['notify_url'] ?? ''),
            ],
            $data ?? $this->normal($payload, $config, $params)
        ));

        Logger::info('[Wechat][Pay][Jsapi][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(Collection $payload, array $config, array $params): array
    {
        return [
            'appid' => $payload->get('appid', $config[get_wechat_type_key($params)] ?? ''),
            'mchid' => $payload->get('mchid', $config['mch_id'] ?? ''),
        ];
    }

    protected function service(Collection $payload, array $config, array $params): array
    {
        $data = [
            'sp_appid' => $payload->get('sp_appid', $config[get_wechat_type_key($params)] ?? ''),
            'sp_mchid' => $payload->get('sp_mchid', $config['mch_id'] ?? ''),
            'sub_mchid' => $payload->get('sub_mchid', $config['sub_mch_id'] ?? ''),
        ];

        if ($payload->has('payer.sub_openid')) {
            $data['sub_appid'] = $payload->get('sub_appid', $config['sub_'.get_wechat_type_key($params)] ?? '');
        }

        return $data;
    }
}
