<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Member\Ocr;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/0aggs5?pathHash=084101d3&ref=api
 */
class DetectPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Member][Ocr][DetectPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'datadigital.fincloud.generalsaas.ocr.common.detect',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Member][Ocr][DetectPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
