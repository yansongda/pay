<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\QrCode;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=792&apiservId=468&version=V2.2&bussType=0
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][QrCode][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        $rocket->mergePayload([
            '_url' => 'gateway/api/backTransReq.do',
            '_sandbox_url' => 'https://101.231.204.80:5000/gateway/api/backTransReq.do',
            'accessType' => $payload?->get('accessType') ?? '0',
            'bizType' => $payload?->get('bizType') ?? '000000',
            'txnType' => $payload?->get('txnType') ?? '00',
            'txnSubType' => $payload?->get('txnSubType') ?? '00',
        ]);

        Logger::info('[Unipay][QrCode][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
