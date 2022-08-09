<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\User;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/02fkan?ref=api&scene=35
 */
class AgreementPageSignPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][AgreementPageSignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setDirection(ResponseParser::class)
            ->mergePayload([
                'method' => 'alipay.user.agreement.page.sign',
                'biz_content' => array_merge(
                    ['product_code' => 'CYCLE_PAY_AUTH'],
                    $rocket->getParams()
                ),
            ]);

        Logger::info('[alipay][AgreementPageSignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
