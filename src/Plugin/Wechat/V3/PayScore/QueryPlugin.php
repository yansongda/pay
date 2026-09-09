<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\PayScore;

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

/**
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012587902
 */
class QueryPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][PayScore][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();

        $outOrderNo = $payload?->get('out_order_no') ?? null;
        $queryId = $payload?->get('query_id') ?? null;

        if ((empty($outOrderNo) && empty($queryId)) || (!empty($outOrderNo) && !empty($queryId))) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询支付分订单，`out_order_no` 与 `query_id` 不允许都填写或都不填写');
        }

        /** @var WechatConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_WECHAT, $params);

        $serviceId = $payload?->get('service_id') ?? $config->getServiceId();

        if (is_null($serviceId) || '' === $serviceId) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING, '参数异常: 缺少支付分服务ID -- [service_id]');
        }

        $appid = $payload?->get('appid') ?? $config->getAppIdByType($params['_type'] ?? 'mp') ?? '';

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => '/v3/payscore/serviceorder?'.http_build_query(array_filter([
                'out_order_no' => $outOrderNo,
                'query_id' => $queryId,
                'service_id' => $serviceId,
                'appid' => $appid,
            ])),
        ]);

        Logger::info('[Wechat][V3][PayScore][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
