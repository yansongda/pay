<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Trade;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

use function Yansongda\Pay\get_douyin_client_token;

class ObtainClientTokenPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][Trade][ObtainClientTokenPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        $token = $params['_access_token'] ?? '';

        if (empty($token)) {
            $token = get_douyin_client_token($params);
        }

        $rocket->mergePayload(['_access_token' => $token]);

        Logger::info('[Douyin][V1][Trade][ObtainClientTokenPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
