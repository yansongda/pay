<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;

class StringInvokeResponsePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[alipay][StringInvokeResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        $response = $this->buildHtml($rocket->getPayload());

        $rocket->setDestination($response);

        Logger::info('[alipay][StringInvokeResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    protected function buildHtml(Collection $payload): Response
    {
        return new Response(200, [], Arr::query($payload->all()));
    }
}
