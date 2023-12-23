<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class AddPayloadBodyPlugin implements PluginInterface
{
    protected JsonPacker $jsonPacker;

    public function __construct(?JsonPacker $jsonPacker = null)
    {
        $this->jsonPacker = $jsonPacker ?? new JsonPacker();
    }

    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][AddPayloadBodyPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload(['_body' => $this->getBody($rocket->getPayload())]);

        Logger::info('[Wechat][AddPayloadBodyPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getBody(?Collection $payload): string
    {
        return (is_null($payload) || 0 === $payload->count()) ? '' : $this->jsonPacker->pack($payload->all());
    }
}
