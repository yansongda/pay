<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Open\Authorization;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * 第三方应用授权：查询应用授权令牌（app_auth_token）.
 *
 * @see https://opendocs.alipay.com/isv/04hgcp
 */
class TokenAppQueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Open][Authorization][TokenAppQueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.open.auth.token.app.query',
            'biz_content' => $rocket->getParams(),
        ]);

        $rocket->getPayload()->forget('app_auth_token');

        Logger::info('[Alipay][Open][Authorization][TokenAppQueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
