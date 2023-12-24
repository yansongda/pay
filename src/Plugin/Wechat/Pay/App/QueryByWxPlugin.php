<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\App;

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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/in-app-payment/query-by-wx-trade-no.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-in-app-payment/query-by-wx-trade-no.html
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
        Logger::debug('[Wechat][Pay][App][QueryByWxPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);

        if (empty($params['transaction_id'])) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: App 通过微信订单号查询订单，参数缺少 `transaction_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/pay/transactions/id/'.$params['transaction_id'].'?'.$this->normal($config),
            '_service_url' => 'v3/pay/partner/transactions/id/'.$params['transaction_id'].'?'.$this->service($config),
        ]);

        Logger::info('[Wechat][Pay][App][QueryByWxPlugin] 插件装载完毕', ['rocket' => $rocket]);

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