<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Openapi\Oauth;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://developers.weixin.qq.com/doc/service/api/webdev/access/api_snsrefreshtoken.html
 */
class RefreshTokenPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Openapi][Oauth][RefreshTokenPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_WECHAT, $params);

        $refreshToken = $params['refresh_token'] ?? null;
        if (empty($refreshToken)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少微信网页授权参数 -- [refresh_token]');
        }

        $appid = $params['appid'] ?? $config->getMpAppId();
        if (empty($appid)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mp_app_id]');
        }

        $rocket->mergePayload([
            '_method' => 'GET',
            '_url' => '/sns/oauth2/refresh_token?'.http_build_query([
                'appid' => $appid,
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]),
            '_body' => '',
        ]);

        Logger::info('[Wechat][Openapi][Oauth][RefreshTokenPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
