<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions;

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
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012647401
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
        Logger::debug('[Wechat][V3][PayScore][Permissions][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_WECHAT, $params);

        $authorizationCode = $payload?->get('authorization_code') ?? null;

        if (empty($authorizationCode)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询支付分预授权（签约），参数缺少 `authorization_code`');
        }

        $serviceId = $payload->get('service_id') ?? $config->getServiceId();

        if (is_null($serviceId) || '' === $serviceId) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING, '参数异常: 缺少支付分服务ID -- [service_id]');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => '/v3/payscore/permissions/authorization-code/'.$authorizationCode.'?'.http_build_query(['service_id' => $serviceId]),
        ]);

        Logger::info('[Wechat][V3][PayScore][Permissions][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
