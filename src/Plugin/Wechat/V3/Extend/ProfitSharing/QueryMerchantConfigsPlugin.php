<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://pay.weixin.qq.com/docs/partner/apis/profit-sharing/merchants/query-merchant-ratio.html
 */
class QueryMerchantConfigsPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][ProfitSharing][QueryMerchantConfigsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $rocket->getParams());
        $subMchId = $payload?->get('sub_mch_id') ?? ($config->getSubMchId() ?? 'null');

        if (Pay::MODE_NORMAL === $config->getMode()) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE, '参数异常: 查询最大分账比例，只支持服务商模式，当前配置为普通商户模式');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_service_url' => 'v3/profitsharing/merchant-configs/'.$subMchId,
        ]);

        Logger::info('[Wechat][Extend][ProfitSharing][QueryMerchantConfigsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
