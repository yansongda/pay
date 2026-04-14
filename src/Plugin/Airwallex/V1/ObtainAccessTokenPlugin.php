<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

use function Yansongda\Pay\get_airwallex_access_token;

class ObtainAccessTokenPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Airwallex][V1][ObtainAccessTokenPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_access_token' => get_airwallex_access_token($rocket->getParams()),
        ]);

        Logger::info('[Airwallex][V1][ObtainAccessTokenPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
