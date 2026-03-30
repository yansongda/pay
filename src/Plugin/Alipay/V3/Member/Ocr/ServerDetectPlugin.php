<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Member\Ocr;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://github.com/alipay/alipay-sdk-php-all/blob/master/v3/docs/Api/DatadigitalFincloudGeneralsaasOcrServerApi.md
 */
class ServerDetectPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Member][Ocr][ServerDetectPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        unset($params['_multipart']);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/datadigital/fincloud/generalsaas/ocr/server/detect',
            '_body' => $params,
        ]);

        Logger::info('[Alipay][V3][Member][Ocr][ServerDetectPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
