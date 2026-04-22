<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\App;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/in-app-payment/query-by-out-refund-no.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-in-app-payment/query-by-out-refund-no.html
 */
class QueryRefundPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Pay][App][QueryRefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();
        $outRefundNo = $payload?->get('out_refund_no') ?? null;

        if (empty($outRefundNo)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: App 查询退款订单，参数缺少 `out_refund_no`');
        }

        $subMchId = $payload->get('sub_mchid', $config->getSubMchId() ?? '');

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/refund/domestic/refunds/'.$outRefundNo,
            '_service_url' => 'v3/refund/domestic/refunds/'.$outRefundNo.'?sub_mchid='.$subMchId,
        ]);

        Logger::info('[Wechat][V3][Pay][App][QueryRefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
