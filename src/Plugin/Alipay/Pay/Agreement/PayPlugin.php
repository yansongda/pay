<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Agreement;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\SupportServiceProviderTrait;

/**
 * @see https://opendocs.alipay.com/open/38d751b1_alipay.trade.pay?pathHash=0a98c4e0&ref=api&scene=b4d9c9906e14451b99c8de390ae20fea
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
        Logger::debug('[alipay][pay][agreement][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->loadAlipayServiceProvider($rocket);

        $rocket->mergePayload([
            'method' => 'alipay.trade.pay',
            'biz_content' => array_merge(
                [
                    'product_code' => 'GENERAL_WITHHOLDING',
                ],
                $rocket->getParams(),
            ),
        ]);

        Logger::info('[alipay][pay][agreement][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
