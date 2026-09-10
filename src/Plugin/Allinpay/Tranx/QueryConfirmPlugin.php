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
 * @see https://prodoc.allinpay.com/doc/2590/ 通联交易确认查询接口（/tranx/queryconfirm），version 默认填 12
 */
class QueryConfirmPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Allinpay][QueryConfirmPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        if (empty($params['reqsn']) && empty($params['trxid'])) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通联交易确认查询缺少必要参数 reqsn/trxid 其一');
        }

        $rocket->mergePayload([
            '_url' => '/tranx/queryconfirm',
            'reqsn' => $params['reqsn'] ?? null,
            'trxid' => $params['trxid'] ?? null,
            'version' => $params['version'] ?? '12',
        ]);

        Logger::info('[Allinpay][QueryConfirmPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
