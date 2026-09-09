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
 * @see https://developers.weixin.qq.com/miniprogram/dev/server/API/user-login/api_code2session.html
 */
class Code2SessionPlugin implements PluginInterface
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
        Logger::debug('[Wechat][Openapi][Oauth][Code2SessionPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_WECHAT, $params);

        $jsCode = $params['js_code'] ?? '';

        if (empty($jsCode)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少微信小程序登录参数 -- [js_code]');
        }

        $appid = $params['appid'] ?? $config->getMiniAppId();
        $secret = $params['secret'] ?? $config->getMiniAppSecret();

        if (empty($appid) || empty($secret)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mini_app_secret] 或 [mini_app_id]');
        }

        $rocket->mergePayload([
            '_method' => 'GET',
            '_url' => '/sns/jscode2session?'.http_build_query(['appid' => $appid, 'secret' => $secret, 'js_code' => $jsCode, 'grant_type' => 'authorization_code']),
            '_body' => '',
        ]);

        Logger::info('[Wechat][Openapi][Oauth][Code2SessionPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
