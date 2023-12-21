<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\App;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\SupportServiceProviderTrait;

/**
 * @see https://opendocs.alipay.com/open/cd12c885_alipay.trade.app.pay?pathHash=c0e35284&ref=api&scene=20
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
        Logger::debug('[Alipay][Pay][App][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->loadAlipayServiceProvider($rocket);

        $rocket->setDirection(ResponseDirection::class)
            ->mergePayload([
                'method' => 'alipay.trade.app.pay',
                'biz_content' => $rocket->getParams(),
            ]);

        Logger::info('[Alipay][Pay][App][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
