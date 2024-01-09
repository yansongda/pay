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
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter3_8.shtml
 */
class ApplyPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws Throwable                随机字符串生成失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V2][Papay][Direct][ApplyPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();

        $rocket->setPacker(XmlPacker::class)
            ->mergePayload([
                '_url' => 'pay/pappayapply',
                '_content_type' => 'application/xml',
                'appid' => $config[get_wechat_type_key($params)] ?? '',
                'mch_id' => $config['mch_id'] ?? '',
                'nonce_str' => Str::random(32),
                'sign_type' => 'MD5',
                'notify_url' => $payload?->get('notify_url') ?? $config['notify_url'] ?? '',
            ]);

        Logger::info('[Wechat][V2][Papay][Direct][ApplyPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
