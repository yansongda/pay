<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Member\FaceVerification;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/02zloc?pathHash=b1141506&ref=api&scene=common
 */
class WapQueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Member][FaceVerification][WapQueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'datadigital.fincloud.generalsaas.face.certify.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Member][FaceVerification][WapQueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
