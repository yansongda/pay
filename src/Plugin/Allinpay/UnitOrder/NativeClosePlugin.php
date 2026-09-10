<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Allinpay\UnitOrder;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

class NativeClosePlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Allinpay][NativeClosePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        if (empty($params['oldreqsn']) && empty($params['oldtrxid'])) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通联主扫关单缺少必要参数 oldreqsn/oldtrxid 其一');
        }

        $rocket->mergePayload([
            '_url' => '/unitorder/closenative',
            'oldreqsn' => $params['oldreqsn'] ?? null,
            'oldtrxid' => $params['oldtrxid'] ?? null,
        ]);

        Logger::info('[Allinpay][NativeClosePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
