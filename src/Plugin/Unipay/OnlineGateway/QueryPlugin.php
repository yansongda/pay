<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\OnlineGateway;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=757&apiservId=448&version=V2.2&bussType=0
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][OnlineGateway][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_url' => 'gateway/api/queryTrans.do',
            'accessType' => '0',
            'bizType' => '000000',
            'txnType' => '00',
            'txnSubType' => '00',
        ]);

        Logger::info('[Unipay][OnlineGateway][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
