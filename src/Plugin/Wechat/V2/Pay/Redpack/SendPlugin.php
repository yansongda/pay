<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2\Pay\Redpack;

use Closure;
use Throwable;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Packer\XmlPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon_sl.php?chapter=13_4&index=3
 */
class SendPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws Throwable                随机字符串生成失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V2][Pay][Redpack][SendPlugin] 插件开始装载', ['rocket' => $rocket]);
        $payload = $rocket->getPayload();
        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);

        if (Pay::MODE_SERVICE === $config->getMode()) {
            $data = $this->service($payload, $config, $params);
        }

        $rocket->setPacker(XmlPacker::class)
            ->mergePayload(array_merge(
                [
                    '_url' => 'mmpaymkttransfers/sendredpack',
                    '_content_type' => 'application/xml',
                    'nonce_str' => Str::random(32),
                    '_http' => [
                        'ssl_key' => $config->getMchSecretCert(),
                        'cert' => $config->getMchPublicCertPath(),
                    ],
                ],
                $data ?? $this->normal($config, $params)
            ));

        Logger::info('[Wechat][V2][Pay][Pos][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(WechatConfig $config, array $params): array
    {
        return [
            'wxappid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
            'mch_id' => $config->getMchId(),
        ];
    }

    protected function service(Collection $payload, WechatConfig $config, array $params): array
    {
        return [
            'wxappid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
            'mch_id' => $config->getMchId(),
            'sub_mch_id' => $payload->get('sub_mch_id', $config->getSubMchId() ?? ''),
            'msgappid' => $config->getSubAppIdByType($params['_type'] ?? 'mp') ?? '',
        ];
    }
}
