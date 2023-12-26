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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/electronic-signature/get-electronic-signature-by-out-no.html
 */
class QueryReceiptPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Transfer][QueryReceiptPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (empty($payload?->get('out_batch_no'))) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询转账账单电子回单接口，参数缺少 `batch_id` 或 `detail_id`');
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'GET',
                '_url' => 'v3/transfer/bill-receipt/'.$payload->get('out_batch_no'),
            ],
        ));

        Logger::info('[Wechat][Marketing][Transfer][QueryReceiptPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
