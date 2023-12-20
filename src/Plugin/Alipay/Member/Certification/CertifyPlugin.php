<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Member\Certification;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/02ahk0?pathHash=b485c65c&ref=api&scene=common
 */
class CertifyPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Member][Certification][CertifyPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.user.certify.open.certify',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Member][Certification][CertifyPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
