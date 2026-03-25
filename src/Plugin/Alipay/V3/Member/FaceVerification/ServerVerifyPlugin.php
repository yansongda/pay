<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://github.com/alipay/alipay-sdk-php-all/blob/master/v3/docs/Api/DatadigitalFincloudGeneralsaasFaceSourceApi.md
 */
class ServerVerifyPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Member][FaceVerification][ServerVerifyPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        unset($params['_multipart']);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/datadigital/fincloud/generalsaas/face/source/certify',
            '_body' => array_merge(
                [
                    'cert_type' => 'IDENTITY_CARD',
                ],
                $params,
            ),
        ]);

        Logger::info('[Alipay][V3][Member][FaceVerification][ServerVerifyPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
