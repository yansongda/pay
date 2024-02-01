<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/09bk7c?pathHash=d86258e3&ref=api&scene=9453b93a5f93490893e7ea5d19d754c9
 */
class PosFreezePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Pay][Authorization][Auth][PosFreezePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.fund.auth.order.freeze',
            'biz_content' => array_merge(
                [
                    'auth_code_type' => 'bar_code',
                    'product_code' => 'PREAUTH_PAY',
                ],
                $rocket->getParams()
            ),
        ]);

        Logger::info('[Alipay][Pay][Authorization][Auth][PosFreezePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
