<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Native;

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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/native-payment/query-by-out-trade-no.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-native-payment/query-by-out-trade-no.html
 */
class QueryPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Native][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $outTradeNo = $rocket->getPayload()?->get('out_trade_no') ?? null;

        if (empty($outTradeNo)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Native 通过商户订单号查询订单，参数缺少 `out_trade_no`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/pay/transactions/out-trade-no/'.$outTradeNo.'?'.$this->normal($config),
            '_service_url' => 'v3/pay/partner/transactions/out-trade-no/'.$outTradeNo.'?'.$this->service($config),
        ]);

        Logger::info('[Wechat][Pay][Native][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(array $config): string
    {
        return http_build_query([
            'mchid' => $config['mch_id'] ?? '',
        ]);
    }

    protected function service(array $config): string
    {
        return http_build_query([
            'sp_mchid' => $config['mch_id'] ?? '',
            'sub_mchid' => $config['sub_mch_id'] ?? '',
        ]);
    }
}
