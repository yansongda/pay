<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Transfer;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/electronic-signature/create-electronic-signature.html
 */
class CreateReceiptPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Transfer][CreateReceiptPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/transfer/bill-receipt',
            ],
        ));

        Logger::info('[Wechat][Marketing][Transfer][CreateReceiptPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
