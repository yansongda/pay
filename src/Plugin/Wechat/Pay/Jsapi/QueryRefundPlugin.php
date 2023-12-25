<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Jsapi;

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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/jsapi-payment/query-by-out-refund-no.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-jsapi-payment/query-by-out-refund-no.html
 */
class QueryRefundPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Jsapi][QueryRefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();

        if (empty($payload?->get('out_refund_no') ?? null)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Jsapi 查询退款订单，参数缺少 `out_refund_no`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/refund/domestic/refunds/'.$payload->get('out_refund_no'),
            '_service_url' => 'v3/refund/domestic/refunds/'.$payload->get('out_refund_no').'?'.$this->service($config),
        ]);

        Logger::info('[Wechat][Pay][Jsapi][QueryRefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function service(array $config): string
    {
        return http_build_query([
            'sub_mchid' => $config['sub_mch_id'] ?? '',
        ]);
    }
}
