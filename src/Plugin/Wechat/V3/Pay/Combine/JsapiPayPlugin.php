<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/orders/jsapi-prepay.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/combine-payment/orders/jsapi-prepay.html
 */
class JsapiPayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Combine][JsapiPayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Jsapi合单 下单，参数为空');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'v3/combine-transactions/jsapi',
            '_service_url' => 'v3/combine-transactions/jsapi',
            'notify_url' => $payload->get('notify_url', $config['notify_url'] ?? ''),
            'combine_appid' => $payload->get('combine_appid', $config[get_wechat_type_key($params)] ?? ''),
            'combine_mchid' => $payload->get('combine_mchid', $config['mch_id'] ?? ''),
        ]);

        Logger::info('[Wechat][Pay][Combine][JsapiPayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
