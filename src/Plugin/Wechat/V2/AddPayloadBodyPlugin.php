<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Packer\XmlPacker;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\filter_params;

class AddPayloadBodyPlugin implements PluginInterface
{
    protected XmlPacker $xmlPacker;

    public function __construct(protected ?XmlPacker $jsonPacker = null)
    {
        $this->xmlPacker ??= new XmlPacker();
    }

    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V2][AddPayloadBodyPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload(['_body' => $this->getBody($rocket->getPayload())]);

        Logger::info('[Wechat][V2][AddPayloadBodyPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getBody(?Collection $payload): string
    {
        return $this->xmlPacker->pack(filter_params($payload->all()));
    }
}
