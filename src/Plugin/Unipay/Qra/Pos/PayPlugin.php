<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Qra\Pos;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Packer\XmlPacker;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_unipay_config;

/**
 * @see https://up.95516.com/open/openapi/doc?index_1=2&index_2=1&chapter_1=274&chapter_2=292
 */
class PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][Qra][Pos][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_unipay_config($params);
        $payload = $rocket->getPayload();

        $rocket->setPacker(XmlPacker::class)
            ->mergePayload([
                '_url' => 'https://qra.95516.com/pay/gateway',
                'encoding' => 'utf-8',
                'signature' => '',
                'bizType' => $payload?->get('bizType') ?? '000000',
                'accessType' => $payload?->get('accessType') ?? '0',
                'merId' => $config['mch_id'] ?? '',
                'currencyCode' => '156',
                'channelType' => $payload?->get('channelType') ?? '08',
                'signMethod' => '01',
                'txnType' => $payload?->get('txnType') ?? '01',
                'txnSubType' => $payload?->get('txnSubType') ?? '06',
                'backUrl' => $payload?->get('backUrl') ?? $config['notify_url'] ?? '',
                'version' => '5.1.0',
            ]);

        Logger::info('[Unipay][Qra][Pos][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
