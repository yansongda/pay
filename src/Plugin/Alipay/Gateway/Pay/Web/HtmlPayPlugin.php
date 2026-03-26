<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Gateway\Pay\Web;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Traits\SupportServiceProviderTrait;

class HtmlPayPlugin implements PluginInterface
{
    use SupportServiceProviderTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Gateway][Pay][Web][HtmlPayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->loadAlipayServiceProvider($rocket);

        $rocket->setDirection(ResponseDirection::class)
            ->mergePayload([
                'method' => 'alipay.trade.page.pay',
                'biz_content' => array_merge(
                    [
                        'product_code' => 'FAST_INSTANT_TRADE_PAY',
                    ],
                    $rocket->getParams()
                ),
            ]);

        Logger::info('[Alipay][Gateway][Pay][Web][HtmlPayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
