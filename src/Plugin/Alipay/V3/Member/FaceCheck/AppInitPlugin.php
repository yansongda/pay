<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Member\FaceCheck;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://github.com/alipay/alipay-sdk-php-all/blob/master/v3/docs/Api/DatadigitalFincloudGeneralsaasFaceCheckApi.md
 */
class AppInitPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Member][FaceCheck][AppInitPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/datadigital/fincloud/generalsaas/face/check/initialize',
            '_body' => array_merge(
                [
                    'biz_code' => 'DATA_DIGITAL_BIZ_CODE_FACE_CHECK_LIVE',
                ],
                $rocket->getParams(),
            ),
        ]);

        Logger::info('[Alipay][V3][Member][FaceCheck][AppInitPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
