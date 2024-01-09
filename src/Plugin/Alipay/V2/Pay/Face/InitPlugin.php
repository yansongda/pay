<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Pay\Face;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/2f7c1d5f_zoloz.authentication.smilepay.initialize?pathHash=24de8b36&ref=api&scene=common
 */
class InitPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Pay][Face][InitPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'zoloz.authentication.smilepay.initialize',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Pay][Face][InitPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
