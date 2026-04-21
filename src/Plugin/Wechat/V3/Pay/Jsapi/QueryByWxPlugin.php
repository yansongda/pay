<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi;

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
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/jsapi-payment/query-by-wx-trade-no.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-jsapi-payment/query-by-wx-trade-no.html
 */
class QueryByWxPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Jsapi][QueryByWxPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();
        $transactionId = $payload?->get('transaction_id') ?? null;

        if (empty($transactionId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Jsapi 通过微信订单号查询订单，参数缺少 `transaction_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/pay/transactions/id/'.$transactionId.'?'.$this->normal($config),
            '_service_url' => 'v3/pay/partner/transactions/id/'.$transactionId.'?'.$this->service($payload, $config),
        ]);

        Logger::info('[Wechat][Pay][Jsapi][QueryByWxPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(WechatConfig $config): string
    {
        return http_build_query([
            'mchid' => $config->getMchId(),
        ]);
    }

    protected function service(Collection $payload, WechatConfig $config): string
    {
        return http_build_query([
            'sp_mchid' => $config->getMchId(),
            'sub_mchid' => $payload->get('sub_mchid', $config->getSubMchId() ?? 'null'),
        ]);
    }
}
