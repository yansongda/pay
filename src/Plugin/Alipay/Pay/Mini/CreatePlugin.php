<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Mini;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\SupportServiceProviderTrait;

/**
 * @see https://opendocs.alipay.com/mini/05x9kv?pathHash=779dc517&ref=api&scene=de4d6a1e0c6e423b9eefa7c3a6dcb7a5
 */
class CreatePlugin implements PluginInterface
{
    use SupportServiceProviderTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pay][mini][CreatePlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->loadAlipayServiceProvider($rocket);

        $rocket->mergePayload([
            'method' => 'alipay.trade.create',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][pay][mini][CreatePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
