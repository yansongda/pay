<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Transfer;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-batch/get-transfer-batch-by-no.html
 */
class QueryBatchIdPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Transfer][QueryBatchIdPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $batchId = $payload?->get('batch_id') ?? null;

        if (empty($batchId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通过微信批次单号查询批次单，参数缺少 `batch_id`');
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'GET',
                '_url' => 'v3/transfer/batches/batch-id/'.$batchId.'?'.$this->normal($payload),
            ],
        ));

        Logger::info('[Wechat][Marketing][Transfer][QueryBatchIdPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(?Collection $payload): string
    {
        return $payload?->query() ?? '';
    }
}
