<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://www.airwallex.com/docs/api/introduction
 */
class GetAccessTokenPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Airwallex][V1][GetAccessTokenPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'api/v1/authentication/login',
            '_auth_type' => 'client',
        ]);

        Logger::info('[Airwallex][V1][GetAccessTokenPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
