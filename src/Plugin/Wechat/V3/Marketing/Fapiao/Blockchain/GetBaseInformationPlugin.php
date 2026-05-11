<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012538180
 */
class GetBaseInformationPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Fapiao][Blockchain][GetBaseInformationPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/new-tax-control-fapiao/merchant/base-information',
        ]);

        Logger::info('[Wechat][V3][Marketing][Fapiao][Blockchain][GetBaseInformationPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
