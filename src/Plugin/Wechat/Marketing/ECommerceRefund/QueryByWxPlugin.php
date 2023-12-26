<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\ECommerceRefund;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

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
        Logger::debug('[Wechat][Marketing][ECommerceRefund][QueryByWxPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = get_wechat_config($params);
        $refundId = $payload?->get('refund_id') ?? null;

        if (is_null($refundId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 平台收付通（退款）-查询单笔退款（按微信支付退款单号），缺少必要参数 `refund_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_service_url' => 'v3/ecommerce/refunds/id/'.$refundId.'?sub_mchid='.$payload->get('sub_mchid', $config['sub_mch_id']),
        ]);

        Logger::info('[Wechat][Marketing][ECommerceRefund][QueryByWxPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
