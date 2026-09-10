<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Allinpay\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

class PayPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Allinpay][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        if (empty($params['reqsn']) || empty($params['paytype'])) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通联统一支付缺少必要参数 reqsn/paytype');
        }

        $rocket->mergePayload([
            '_url' => '/unitorder/pay',
            'reqsn' => $params['reqsn'],
            'paytype' => $params['paytype'],
        ]);

        Logger::info('[Allinpay][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
