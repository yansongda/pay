<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Allinpay\Tranx;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

/**
 * @see https://prodoc.allinpay.com/doc/983/ 通联交易关单接口（/tranx/close），version 默认填 12
 */
class ClosePlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Allinpay][ClosePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        if (empty($params['oldreqsn']) && empty($params['oldtrxid'])) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通联关单缺少必要参数 oldreqsn/oldtrxid 其一');
        }

        $rocket->mergePayload([
            '_url' => '/tranx/close',
            'oldreqsn' => $params['oldreqsn'] ?? null,
            'oldtrxid' => $params['oldtrxid'] ?? null,
            'version' => $params['version'] ?? '12',
        ]);

        Logger::info('[Allinpay][ClosePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
