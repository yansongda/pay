<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\User;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/02fkap?ref=api&scene=90766afb41f74df6ae1676e89625ebac
 */
class AgreementUnsignPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][AgreementUnsignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.user.agreement.unsign',
            'biz_content' => array_merge(
                ['personal_product_code' => 'CYCLE_PAY_AUTH_P'],
                $rocket->getParams()
            ),
        ]);

        Logger::info('[alipay][AgreementUnsignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
