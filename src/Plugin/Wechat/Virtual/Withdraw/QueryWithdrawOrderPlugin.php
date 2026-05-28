<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/virtual-payment.html
 */
class QueryWithdrawOrderPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][Withdraw][QueryWithdrawOrderPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付查询提现单，参数为空');
        }

        if (!$payload->has('withdraw_order_id') && !$payload->has('out_trade_no')) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付查询提现单，参数缺少必要参数');
        }

        $data = [
            '_method' => 'POST',
            '_url' => '/xpay/query_withdraw_order',
        ];

        if ($payload->has('withdraw_order_id')) {
            $data['withdraw_order_id'] = $payload->get('withdraw_order_id');
        }

        if ($payload->has('out_trade_no')) {
            $data['out_trade_no'] = $payload->get('out_trade_no');
        }

        $rocket->mergePayload($data);

        Logger::info('[Wechat][Virtual][Withdraw][QueryWithdrawOrderPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
