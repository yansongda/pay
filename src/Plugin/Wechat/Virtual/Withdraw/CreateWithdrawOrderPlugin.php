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
 * @see https://developers.weixin.qq.com/miniprogram/dev/server/API/VirtualPayment/api_create_withdraw_order
 */
class CreateWithdrawOrderPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][Withdraw][CreateWithdrawOrderPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付创建提现单，参数为空');
        }

        $withdrawNo = $payload->get('withdraw_no');

        if (empty($withdrawNo)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付创建提现单，缺少 withdraw_no');
        }

        $data = [
            '_method' => 'POST',
            '_url' => '/xpay/create_withdraw_order',
            'withdraw_no' => $withdrawNo,
        ];

        $withdrawAmount = $payload->get('withdraw_amount');
        if (isset($withdrawAmount)) {
            $data['withdraw_amount'] = $withdrawAmount;
        }

        $rocket->mergePayload($data);

        Logger::info('[Wechat][Virtual][Withdraw][CreateWithdrawOrderPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
