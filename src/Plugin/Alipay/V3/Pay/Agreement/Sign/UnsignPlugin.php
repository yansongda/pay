<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Pay\Agreement\Sign;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://github.com/alipay/alipay-sdk-php-all/blob/master/v3/docs/Api/AlipayUserAgreementApi.md
 */
class UnsignPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Pay][Agreement][Sign][UnsignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/alipay/user/agreement/unsign',
            '_body' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][V3][Pay][Agreement][Sign][UnsignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
