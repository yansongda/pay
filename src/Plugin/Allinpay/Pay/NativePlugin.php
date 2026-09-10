<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Allinpay\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

class NativePlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Allinpay][NativePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        if (empty($params['reqsn']) || empty($params['trxamt']) || empty($params['expiretime'])) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通联主扫支付缺少必要参数 reqsn/trxamt/expiretime');
        }

        $rocket->mergePayload([
            '_url' => '/unitorder/nativepay',
            'reqsn' => $params['reqsn'],
            'trxamt' => $params['trxamt'],
            'expiretime' => $params['expiretime'],
        ]);

        Logger::info('[Allinpay][NativePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
