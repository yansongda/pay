<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Openapi\Oauth;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://developers.weixin.qq.com/doc/service/api/webdev/access/api_snsuserinfo.html
 */
class UserInfoPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Openapi][Oauth][UserInfoPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        $accessToken = $params['access_token'] ?? null;
        if (empty($accessToken)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少微信网页授权参数 -- [access_token]');
        }

        $openid = $params['openid'] ?? null;
        if (empty($openid)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少微信网页授权参数 -- [openid]');
        }

        $rocket->mergePayload([
            '_method' => 'GET',
            '_url' => '/sns/userinfo?'.http_build_query([
                'access_token' => $accessToken,
                'openid' => $openid,
                'lang' => $params['lang'] ?? 'zh_CN',
            ]),
            '_body' => '',
        ]);

        Logger::info('[Wechat][Openapi][Oauth][UserInfoPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
