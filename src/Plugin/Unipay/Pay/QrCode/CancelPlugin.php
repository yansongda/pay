<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Pay\QrCode;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_unipay_config;

/**
 * @see https://open.unionpay.com/tjweb/acproduct/APIList?apiservId=468&acpAPIId=800&bussType=0
 */
class CancelPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][Pay][QrCode][CancelPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_unipay_config($params);
        $payload = $rocket->getPayload();

        $rocket->mergePayload([
            '_url' => 'gateway/api/backTransReq.do',
            'encoding' => 'utf-8',
            'signature' => '',
            'bizType' => $payload?->get('bizType') ?? '000000',
            'accessType' => $payload?->get('accessType') ?? '0',
            'merId' => $config['mch_id'] ?? '',
            'channelType' => $payload?->get('channelType') ?? '08',
            'signMethod' => '01',
            'txnType' => $payload?->get('txnType') ?? '31',
            'txnSubType' => $payload?->get('txnSubType') ?? '00',
            'backUrl' => $payload->get('backUrl', $config['notify_url'] ?? ''),
            'version' => '5.1.0',
        ]);

        Logger::info('[Unipay][Pay][QrCode][CancelPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
