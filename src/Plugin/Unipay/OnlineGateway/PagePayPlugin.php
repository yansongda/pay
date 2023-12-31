<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\OnlineGateway;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=754&apiservId=448&version=V2.2&bussType=0
 */
class PagePayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][OnlineGateway][PagePayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setDirection(ResponseDirection::class)
            ->mergePayload([
                '_url' => 'gateway/api/frontTransReq.do',
                'accessType' => '0',
                'bizType' => '000201',
                'txnType' => '01',
                'txnSubType' => '01',
                'channelType' => '07',
            ]);

        Logger::info('[Unipay][OnlineGateway][PagePayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
