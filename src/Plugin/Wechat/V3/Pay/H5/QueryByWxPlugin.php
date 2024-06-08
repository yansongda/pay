<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\H5;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_provider_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/h5-payment/query-by-wx-trade-no.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-h5-payment/query-by-wx-trade-no.html
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
        Logger::debug('[Wechat][V3][Pay][H5][QueryByWxPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('wechat', $params);
        $payload = $rocket->getPayload();
        $transactionId = $payload?->get('transaction_id') ?? null;

        if (empty($transactionId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: H5 通过微信订单号查询订单，参数缺少 `transaction_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/pay/transactions/id/'.$transactionId.'?'.$this->normal($config),
            '_service_url' => 'v3/pay/partner/transactions/id/'.$transactionId.'?'.$this->service($payload, $config),
        ]);

        Logger::info('[Wechat][V3][Pay][H5][QueryByWxPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(array $config): string
    {
        return http_build_query([
            'mchid' => $config['mch_id'] ?? 'null',
        ]);
    }

    protected function service(Collection $payload, array $config): string
    {
        return http_build_query([
            'sp_mchid' => $config['mch_id'] ?? 'null',
            'sub_mchid' => $payload->get('sub_mchid', $config['sub_mch_id'] ?? 'null'),
        ]);
    }
}
