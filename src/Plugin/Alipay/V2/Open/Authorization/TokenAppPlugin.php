<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Open\Authorization;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * 第三方应用授权：换取应用授权令牌（app_auth_token）.
 *
 * @see https://opendocs.alipay.com/open/02qq4l
 */
class TokenAppPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Open][Authorization][TokenAppPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.open.auth.token.app',
            'biz_content' => $rocket->getParams(),
        ]);

        $rocket->getPayload()->forget('app_auth_token');

        Logger::info('[Alipay][Open][Authorization][TokenAppPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
