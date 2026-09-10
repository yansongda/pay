<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Allinpay\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AllinpayTrait;

/**
 * @see https://prodoc.allinpay.com/doc/255/ 通联被扫支付接口（/unitorder/scanqrpay），version 默认填 11
 */
class ScanPlugin implements PluginInterface
{
    use AllinpayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Allinpay][ScanPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var AllinpayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALLINPAY, $params);

        if (empty($params['reqsn']) || empty($params['trxamt']) || empty($params['authcode']) || empty($params['terminfo'])) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通联被扫支付缺少必要参数 reqsn/trxamt/authcode/terminfo');
        }

        $rocket->mergePayload([
            '_url' => '/unitorder/scanqrpay',
            'reqsn' => $params['reqsn'],
            'trxamt' => $params['trxamt'],
            'authcode' => $params['authcode'],
            // terminfo 官方契约为 json 字符串；数组入参统一由 AddPayloadSignPlugin 转为 json 字符串
            'terminfo' => $params['terminfo'],
            'version' => $params['version'] ?? '11',
            'notify_url' => $params['_notify_url'] ?? $params['notify_url'] ?? $config->getNotifyUrl(),
        ]);

        Logger::info('[Allinpay][ScanPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
