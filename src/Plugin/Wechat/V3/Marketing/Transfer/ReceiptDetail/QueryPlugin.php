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

use function Yansongda\Artful\filter_params;
use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/electronic-receipt-api/query-electronic-receipt.html
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
        Logger::debug('[Wechat][Marketing][Transfer][ReceiptDetail][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $config = get_wechat_config($rocket->getParams());

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE, '参数异常: 查询转账明细电子回单受理结果API，只支持普通商户模式，当前配置为服务商模式');
        }

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询转账明细电子回单受理结果API，参数为空');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/transfer-detail/electronic-receipts?'.filter_params($payload)->query(),
        ]);

        Logger::info('[Wechat][Marketing][Transfer][ReceiptDetail][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
