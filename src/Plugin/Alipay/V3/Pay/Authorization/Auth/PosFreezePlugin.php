<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Auth;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://github.com/alipay/alipay-sdk-php-all/blob/master/v3/docs/Api/AlipayFundAuthOrderApi.md
 */
class PosFreezePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Pay][Authorization][Auth][PosFreezePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/alipay/fund/auth/order/freeze',
            '_body' => array_merge(
                [
                    'auth_code_type' => 'bar_code',
                    'product_code' => 'PREAUTH_PAY',
                ],
                $rocket->getParams(),
            ),
        ]);

        Logger::info('[Alipay][V3][Pay][Authorization][Auth][PosFreezePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
