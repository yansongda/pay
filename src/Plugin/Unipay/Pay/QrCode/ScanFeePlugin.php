<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Pay\QrCode;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=796&apiservId=468&version=V2.2&bussType=0
 */
class ScanFeePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][QrCode][ScanFeePlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        $rocket->mergePayload([
            '_url' => 'gateway/api/backTransReq.do',
            'accessType' => $payload?->get('accessType') ?? '0',
            'bizType' => $payload?->get('bizType') ?? '000601',
            'txnType' => $payload?->get('txnType') ?? '13',
            'txnSubType' => $payload?->get('txnSubType') ?? '08',
            'channelType' => $payload?->get('channelType') ?? '08',
        ]);

        Logger::info('[Unipay][QrCode][ScanFeePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
