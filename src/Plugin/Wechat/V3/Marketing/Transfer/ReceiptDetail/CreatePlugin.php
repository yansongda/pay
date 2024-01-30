<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\ReceiptDetail;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/electronic-receipt-api/create-electronic-receipt.html
 */
class CreatePlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Transfer][ReceiptDetail][CreatePlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_wechat_config($rocket->getParams());

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE, '参数异常: 受理转账明细电子回单，只支持普通商户模式，当前配置为服务商模式');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'v3/transfer-detail/electronic-receipts',
        ]);

        Logger::info('[Wechat][Marketing][Transfer][ReceiptDetail][CreatePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
