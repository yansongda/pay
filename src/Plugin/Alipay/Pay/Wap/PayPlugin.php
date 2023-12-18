<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Wap;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\SupportServiceProviderTrait;

/**
 * @see https://opendocs.alipay.com/open/29ae8cb6_alipay.trade.wap.pay?pathHash=0a6313c7&ref=api&scene=21
 */
class PayPlugin implements PluginInterface
{
    use SupportServiceProviderTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][wap][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->loadAlipayServiceProvider($rocket);

        $rocket->setDirection(ResponseDirection::class)
            ->mergePayload([
                'method' => 'alipay.trade.wap.pay',
                'biz_content' => array_merge(
                    [
                        'product_code' => 'QUICK_WAP_PAY',
                    ],
                    $rocket->getParams(),
                ),
            ]);

        Logger::info('[alipay][wap][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
