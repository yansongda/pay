<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Member\Ocr;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/05ut8h?pathHash=8cc2aaf1&ref=api&scene=common
 */
class ServerDetectPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Member][Ocr][ServerDetectPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'datadigital.fincloud.generalsaas.ocr.server.detect',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Member][Ocr][ServerDetectPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
