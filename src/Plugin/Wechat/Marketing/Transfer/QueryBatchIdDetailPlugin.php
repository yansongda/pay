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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-detail/get-transfer-detail-by-no.html
 */
class QueryBatchIdDetailPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Transfer][QueryBatchIdDetailPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $batchId = $payload?->get('batch_id') ?? null;
        $detailId = $payload?->get('detail_id') ?? null;

        if (empty($batchId) || empty($detailId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通过微信明细单号查询明细单，参数缺少 `batch_id` 或 `detail_id`');
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'GET',
                '_url' => 'v3/transfer/batches/batch-id/'.$batchId.'/details/detail-id/'.$detailId,
            ],
        ));

        Logger::info('[Wechat][Marketing][Transfer][QueryBatchIdDetailPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
