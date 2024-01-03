<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\filter_params;

class AddPayloadBodyPlugin implements PluginInterface
{
    public function __construct(protected ?JsonPacker $jsonPacker = null)
    {
        $this->jsonPacker ??= new JsonPacker();
    }

    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][AddPayloadBodyPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload(['_body' => $this->getBody($rocket->getPayload())]);

        Logger::info('[Wechat][V3][AddPayloadBodyPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getBody(?Collection $payload): string
    {
        $actualPayload = filter_params($payload->all());

        return empty($actualPayload) ? '' : $this->jsonPacker->pack($actualPayload);
    }
}
