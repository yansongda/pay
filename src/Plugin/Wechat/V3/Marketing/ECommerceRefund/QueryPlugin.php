<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceRefund;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

use function Yansongda\Pay\get_provider_config;

/**
 * @see https://pay.weixin.qq.com/docs/partner/apis/ecommerce-refund/refunds/query-refund-by-out-refund-no.html
 */
class QueryPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][ECommerceRefund][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = get_provider_config('wechat', $params);
        $outRefundNo = $payload?->get('out_refund_no') ?? null;

        if (Pay::MODE_NORMAL === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE, '参数异常: 平台收付通（退款）-查询单笔退款（按商户退款单号），只支持服务商模式，当前配置为普通商户模式');
        }

        if (is_null($outRefundNo)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 平台收付通（退款）-查询单笔退款（按商户退款单号），缺少必要参数 `out_refund_no`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_service_url' => 'v3/ecommerce/refunds/out-refund-no/'.$outRefundNo.'?sub_mchid='.$payload->get('sub_mchid', $config['sub_mch_id'] ?? 'null'),
        ]);

        Logger::info('[Wechat][V3][Marketing][ECommerceRefund][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
