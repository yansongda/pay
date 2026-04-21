<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Qra\Scan;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Packer\XmlPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Traits\ProviderConfigTrait;
use Yansongda\Supports\Str;

/**
 * @see https://up.95516.com/open/openapi/doc?index_1=1&index_2=1&chapter_1=235&chapter_2=256
 */
class RefundPlugin implements PluginInterface
{
    use ProviderConfigTrait;

    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][Qra][Scan][RefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var UnipayConfig $config */
        $config = self::getProviderConfig('unipay', $params);

        $rocket->setPacker(XmlPacker::class)
            ->mergePayload([
                '_url' => 'https://qra.95516.com/pay/gateway',
                'service' => 'unified.trade.refund',
                'charset' => 'UTF-8',
                'sign_type' => 'MD5',
                'mch_id' => $config->getMchId() ?? '',
                'nonce_str' => Str::random(32),
            ]);

        Logger::info('[Unipay][Qra][Scan][RefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
