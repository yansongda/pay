<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Transfer;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-detail/get-transfer-detail-by-out-no.html
 */
class QueryOutBatchNoDetailPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Transfer][QueryOutBatchNoDetailPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (empty($payload?->get('out_batch_no')) || !is_string($payload?->get('out_detail_no'))) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通过商家明细单号查询明细单，参数缺少 `out_batch_no` 或 `out_detail_no`');
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'GET',
                '_url' => 'v3/transfer/batches/out-batch-no/'.$payload->get('out_batch_no').'/details/out-detail-no'.$payload->get('out_detail_no'),
            ],
        ));

        Logger::info('[Wechat][Marketing][Transfer][QueryOutBatchNoDetailPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
