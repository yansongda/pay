<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Rocket;

class PageRefundPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][PageRefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setDirection(ResponseParser::class)
            ->mergePayload([
                'method' => 'alipay.trade.page.refund',
                'biz_content' => $rocket->getParams(),
            ]);

        Logger::info('[alipay][PageRefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
