<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct;

use Closure;
use Throwable;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Packer\XmlPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Str;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter3_8.shtml
 */
class ApplyPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws Throwable                随机字符串生成失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V2][Papay][Direct][ApplyPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();

        $rocket->setPacker(XmlPacker::class)
            ->mergePayload([
                '_url' => 'pay/pappayapply',
                '_content_type' => 'application/xml',
                'appid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
                'mch_id' => $config->getMchId(),
                'nonce_str' => Str::random(32),
                'sign_type' => 'MD5',
                'notify_url' => $payload?->get('notify_url') ?? $config->getNotifyUrl(),
            ]);

        Logger::info('[Wechat][V2][Papay][Direct][ApplyPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
