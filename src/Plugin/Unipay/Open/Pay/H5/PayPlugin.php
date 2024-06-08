<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Open\Pay\H5;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

use function Yansongda\Pay\get_provider_config;

/**
 * @see https://open.unionpay.com/tjweb/acproduct/APIList?apiservId=453&acpAPIId=334&bussType=0
 */
class PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][Pay][H5][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('unipay', $params);
        $payload = $rocket->getPayload();

        $rocket->setDirection(ResponseDirection::class)
            ->mergePayload([
                '_url' => 'gateway/api/frontTransReq.do',
                'encoding' => 'utf-8',
                'signature' => '',
                'bizType' => $payload?->get('bizType') ?? '000201',
                'accessType' => $payload?->get('accessType') ?? '0',
                'currencyCode' => '156',
                'merId' => $config['mch_id'] ?? '',
                'channelType' => $payload?->get('channelType') ?? '07',
                'signMethod' => '01',
                'txnType' => $payload?->get('txnType') ?? '01',
                'txnSubType' => $payload?->get('txnSubType') ?? '01',
                'frontUrl' => $payload?->get('frontUrl') ?? $config['return_url'] ?? '',
                'backUrl' => $payload?->get('backUrl') ?? $config['notify_url'] ?? '',
                'version' => '5.1.0',
            ]);

        Logger::info('[Unipay][Pay][H5][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
