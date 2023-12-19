<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Pos;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\SupportServiceProviderTrait;

/**
 * @see https://opendocs.alipay.com/open/1f1fe18c_alipay.trade.pay?pathHash=29c9a9ba&ref=api&scene=32
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
        Logger::debug('[alipay][pay][pos][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->loadAlipayServiceProvider($rocket);

        $rocket->mergePayload([
            'method' => 'alipay.trade.pay',
            'biz_content' => array_merge(
                [
                    'product_code' => 'FACE_TO_FACE_PAYMENT',
                    'scene' => 'bar_code',
                ],
                $rocket->getParams(),
            ),
        ]);

        Logger::info('[alipay][pay][pos][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
