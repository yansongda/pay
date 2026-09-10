<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Allinpay\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

class ScanPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Allinpay][ScanPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        if (empty($params['reqsn']) || empty($params['authcode']) || empty($params['terminfo'])) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通联被扫支付缺少必要参数 reqsn/authcode/terminfo');
        }

        $terminfo = $params['terminfo'];

        if (is_array($terminfo)) {
            $terminfo = json_encode($terminfo, JSON_UNESCAPED_UNICODE);
        }

        $rocket->mergePayload([
            '_url' => '/unitorder/scanqrpay',
            'reqsn' => $params['reqsn'],
            'authcode' => $params['authcode'],
            'terminfo' => $terminfo,
        ]);

        Logger::info('[Allinpay][ScanPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
