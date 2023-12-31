<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\QrCode;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=798&apiservId=468&version=V2.2&bussType=0
 */
class PosPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][QrCode][PosPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_url' => 'gateway/api/backTransReq.do',
            'bizType' => '000000',
            'accessType' => '0',
            'txnType' => '01',
            'txnSubType' => '06',
            'channelType' => '08',
        ]);

        Logger::info('[Unipay][QrCode][PosPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
