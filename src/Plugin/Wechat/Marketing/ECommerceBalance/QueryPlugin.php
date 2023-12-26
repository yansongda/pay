<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\ECommerceBalance;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/partner/apis/ecommerce-balance/accounts/query-balance.html
 */
class QueryPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][ECommerceBalance][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $accountType = $rocket->getPayload()?->get('account_type') ?? null;

        if (empty($accountType)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询电商平台账户实时余额，参数缺少 `account_type`');
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'GET',
                '_service_url' => 'v3/merchant/fund/balance/'.$accountType,
            ],
        ));

        Logger::info('[Wechat][Marketing][ECommerceBalance][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
