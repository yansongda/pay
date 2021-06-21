<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Rocket;

class TransPagePayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][TransPagePayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setDirection(ResponseParser::class)
            ->mergePayload([
                'method' => 'alipay.fund.trans.page.pay',
                'biz_content' => $rocket->getParams(),
            ]);

        Logger::info('[alipay][TransPagePayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
