<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Paypal\V2;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

class VerifyWebhookSignPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Paypal][V2][VerifyWebhookSignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => $params['_verify_url'] ?? '',
            '_body' => $params['_verify_body'] ?? '',
            '_access_token' => $params['_access_token'] ?? '',
        ]);

        Logger::info('[Paypal][V2][VerifyWebhookSignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
