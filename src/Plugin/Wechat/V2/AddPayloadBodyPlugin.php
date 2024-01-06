<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\filter_params;
use function Yansongda\Pay\get_packer;

class AddPayloadBodyPlugin implements PluginInterface
{
    /**
     * @throws InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V2][AddPayloadBodyPlugin] 插件开始装载', ['rocket' => $rocket]);

        $packer = get_packer($rocket->getPacker());

        $rocket->mergePayload(['_body' => $packer->pack(filter_params($rocket->getPayload()))]);

        Logger::info('[Wechat][V2][AddPayloadBodyPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
