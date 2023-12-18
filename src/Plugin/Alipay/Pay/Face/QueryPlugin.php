<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Face;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/c8e4d285_zoloz.authentication.customer.ftoken.query?pathHash=4cba522d&ref=api&scene=common
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][face][DetailPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'zoloz.authentication.customer.ftoken.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][face][DetailPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
