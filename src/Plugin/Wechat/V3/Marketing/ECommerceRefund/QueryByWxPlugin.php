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

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/partner/apis/ecommerce-refund/refunds/query-refund.html
 */
class QueryByWxPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][ECommerceRefund][QueryBatchByWxPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = get_wechat_config($params);
        $refundId = $payload?->get('refund_id') ?? null;

        if (Pay::MODE_NORMAL === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE, '参数异常: 平台收付通（退款）-查询单笔退款（按微信支付退款单号），只支持服务商模式，当前配置为普通商户模式');
        }

        if (is_null($refundId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 平台收付通（退款）-查询单笔退款（按微信支付退款单号），缺少必要参数 `refund_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_service_url' => 'v3/ecommerce/refunds/id/'.$refundId.'?sub_mchid='.$payload->get('sub_mchid', $config['sub_mch_id'] ?? 'null'),
        ]);

        Logger::info('[Wechat][Marketing][ECommerceRefund][QueryBatchByWxPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
