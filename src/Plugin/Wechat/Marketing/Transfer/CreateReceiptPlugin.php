<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Transfer;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/electronic-signature/create-electronic-signature.html
 */
class CreateReceiptPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Transfer][CreateReceiptPlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_wechat_config($rocket->getParams());

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE, '参数异常: 转账账单电子回单申请受理接口，只支持普通商户模式，当前配置为服务商模式');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'v3/transfer/bill-receipt',
        ]);

        Logger::info('[Wechat][Marketing][Transfer][CreateReceiptPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
