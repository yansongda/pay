<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\QrCode;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=797&apiservId=468&version=V2.2&bussType=0
 */
class PosPreAuthPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][QrCode][PosPreAuthPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        $rocket->mergePayload([
            '_url' => 'gateway/api/backTransReq.do',
            'accessType' => $payload?->get('accessType') ?? '0',
            'bizType' => $payload?->get('bizType') ?? '000201',
            'txnType' => $payload?->get('txnType') ?? '02',
            'txnSubType' => $payload?->get('txnSubType') ?? '04',
            'channelType' => $payload?->get('channelType') ?? '08',
        ]);

        Logger::info('[Unipay][QrCode][PosPreAuthPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
