<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\QrCode;

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

        $rocket->mergePayload([
            '_url' => 'gateway/api/backTransReq.do',
            'accessType' => '0',
            'bizType' => '000601',
            'txnType' => '13',
            'txnSubType' => '08',
            'channelType' => '08',
        ]);

        Logger::info('[Unipay][QrCode][ScanFeePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
