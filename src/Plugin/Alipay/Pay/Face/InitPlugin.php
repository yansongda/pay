<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Face;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/2f7c1d5f_zoloz.authentication.smilepay.initialize?pathHash=24de8b36&ref=api&scene=common
 */
class InitPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pay][face][InitPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'zoloz.authentication.smilepay.initialize',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][pay][face][InitPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}