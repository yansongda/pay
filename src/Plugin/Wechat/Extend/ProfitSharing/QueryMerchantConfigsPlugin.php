<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Extend\ProfitSharing;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/partner/apis/profit-sharing/merchants/query-merchant-ratio.html
 */
class QueryMerchantConfigsPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][ProfitSharing][QueryMerchantConfigsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_wechat_config($rocket->getParams());

        $rocket->setPayload(array_merge(
            [
                '_method' => 'GET',
                '_service_url' => 'v3/profitsharing/merchant-configs/'.$config['sub_mch_id'],
            ],
        ));

        Logger::info('[Wechat][Extend][ProfitSharing][QueryMerchantConfigsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
