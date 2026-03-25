<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://github.com/alipay/alipay-sdk-php-all/blob/master/v3/docs/Api/DatadigitalFincloudGeneralsaasFaceVerificationApi.md
 */
class AppInitPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Member][FaceVerification][AppInitPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/datadigital/fincloud/generalsaas/face/verification/initialize',
            '_body' => array_merge(
                [
                    'biz_code' => 'DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION',
                    'identity_type' => 'CERT_INFO',
                ],
                $rocket->getParams(),
            ),
        ]);

        Logger::info('[Alipay][V3][Member][FaceVerification][AppInitPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
