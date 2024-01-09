<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Member\FaceVerification;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/04jg6r?pathHash=0572cc86&ref=api&scene=common
 */
class AppInitPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Member][FaceVerification][AppInitPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'datadigital.fincloud.generalsaas.face.verification.initialize',
            'biz_content' => array_merge(
                [
                    'biz_code' => 'DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION',
                    'identity_type' => 'CERT_INFO',
                ],
                $rocket->getParams(),
            ),
        ]);

        Logger::info('[Alipay][Member][FaceVerification][AppInitPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
