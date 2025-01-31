<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

use function Yansongda\Pay\get_provider_config;

/**
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012716457
 */
class QueryByWxPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][MchTransfer][QueryByWxPlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_provider_config('wechat', $rocket->getParams());
        $payload = $rocket->getPayload();
        $transferBillNo = $payload?->get('transfer_bill_no') ?? null;

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE, '参数异常: 通过微信单号查询转账单，只支持普通商户模式，当前配置为服务商模式');
        }

        if (empty($transferBillNo)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通过微信单号查询转账单，参数缺少 `transfer_bill_no`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/fund-app/mch-transfer/transfer-bills/transfer-bill-no/'.$transferBillNo,
        ]);

        Logger::info('[Wechat][Marketing][MchTransfer][QueryByWxPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
