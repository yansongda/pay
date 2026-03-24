<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Pay\Web;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

class PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Pay][Web][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload(array_merge(
            [
                '_url' => '/v3/alipay/trade/page/pay',
                '_method' => 'POST',
                'product_code' => 'FAST_INSTANT_TRADE_PAY',
            ],
            $rocket->getParams()
        ));

        Logger::info('[Alipay][V3][Pay][Web][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
