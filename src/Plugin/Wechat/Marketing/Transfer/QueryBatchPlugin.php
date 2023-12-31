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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-batch/get-transfer-batch-by-out-no.html
 */
class QueryBatchPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Transfer][QueryBatchPlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_wechat_config($rocket->getParams());
        $payload = $rocket->getPayload();
        $outBatchNo = $payload?->get('out_batch_no') ?? null;

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE, '参数异常: 通过商家批次单号查询批次单，只支持普通商户模式，当前配置为服务商模式');
        }

        if (empty($outBatchNo)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通过商家批次单号查询批次单，参数缺少 `out_batch_no`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/transfer/batches/out-batch-no/'.$outBatchNo.'?'.$payload->except('out_batch_no')->query(),
        ]);

        Logger::info('[Wechat][Marketing][Transfer][QueryBatchPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
