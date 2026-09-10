<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\OriginResponseDirection;
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
 * 按协议号（authorization_code）解除授权.
 *
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012647410
 * 按 openid 解除授权
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012647413
 */
class TerminatePlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][PayScore][Permissions][TerminatePlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_WECHAT, $params);

        if (Pay::MODE_SERVICE === $config->getMode()) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE, '参数异常: 解除支付分预授权（签约），只支持普通商户模式，当前配置为服务商模式');
        }

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 解除支付分预授权（签约），参数为空');
        }

        $serviceId = $payload->get('service_id') ?? $config->getServiceId();

        if (is_null($serviceId) || '' === $serviceId) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING, '参数异常: 缺少支付分服务ID -- [service_id]');
        }

        $openid = $payload->get('openid') ?? null;
        $authorizationCode = $payload->get('authorization_code') ?? null;

        if (empty($openid) && empty($authorizationCode)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 解除支付分预授权（签约），参数缺少 `openid` 或 `authorization_code`');
        }

        if (!empty($openid) && !empty($authorizationCode)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 解除支付分预授权（签约），`openid` 与 `authorization_code` 不允许同时填写');
        }

        // 路径参数不得进入 body
        $rocket->getPayload()->forget(['openid', 'authorization_code']);

        if (!empty($openid)) {
            $appid = $payload->get('appid') ?? $config->getAppIdByType($params['_type'] ?? 'mp');

            if (empty($appid)) {
                throw new InvalidParamsException(Exception::PARAMS_WECHAT_APPID_MISSING, '参数异常: 缺少公众账号ID -- [appid]');
            }

            $rocket->setDirection(OriginResponseDirection::class)->mergePayload([
                '_method' => 'POST',
                '_url' => '/v3/payscore/permissions/openid/'.$openid.'/terminate',
                'appid' => $appid,
                'service_id' => $serviceId,
            ]);
        } else {
            $rocket->setDirection(OriginResponseDirection::class)->mergePayload([
                '_method' => 'POST',
                '_url' => '/v3/payscore/permissions/authorization-code/'.$authorizationCode.'/terminate',
                'service_id' => $serviceId,
            ]);
        }

        Logger::info('[Wechat][V3][PayScore][Permissions][TerminatePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
