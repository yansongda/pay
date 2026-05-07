<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\AirwallexTrait;

/**
 * @see https://www.airwallex.com/docs/developer-tools/webhooks/listen-for-webhook-events
 */
class VerifyWebhookSignPlugin implements PluginInterface
{
    use AirwallexTrait;

    /**
     * @throws InvalidParamsException
     * @throws InvalidSignException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Airwallex][V1][VerifyWebhookSignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $request = $params['_request'] ?? $rocket->getDestinationOrigin();

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: Airwallex 回调请求不正确');
        }

        self::verifyAirwallexWebhookSign($request, $params);

        Logger::info('[Airwallex][V1][VerifyWebhookSignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
