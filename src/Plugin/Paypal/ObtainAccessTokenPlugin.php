<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Paypal\V2;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Traits\PaypalTrait;

class ObtainAccessTokenPlugin implements PluginInterface
{
    use PaypalTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Paypal][V2][ObtainAccessTokenPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        $token = $params['_access_token'] ?? '';

        if (empty($token)) {
            $token = self::getPaypalAccessToken($params);
        }

        $rocket->mergePayload(['_access_token' => $token]);

        Logger::info('[Paypal][V2][ObtainAccessTokenPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
