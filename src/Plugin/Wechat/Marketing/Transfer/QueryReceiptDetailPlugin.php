<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Transfer;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/electronic-receipt-api/query-electronic-receipt.html
 */
class QueryReceiptDetailPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Transfer][QueryReceiptDetailPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setPayload(array_merge(
            [
                '_method' => 'GET',
                '_url' => 'v3/transfer-detail/electronic-receipts?'.$this->normal($rocket->getPayload()),
            ],
        ));

        Logger::info('[Wechat][Marketing][Transfer][QueryReceiptDetailPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(?Collection $payload): string
    {
        return $payload?->query() ?? '';
    }
}
