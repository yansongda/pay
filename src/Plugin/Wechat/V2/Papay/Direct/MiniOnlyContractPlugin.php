<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Traits\WechatTrait;


/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter3_3.shtml
 */
class MiniOnlyContractPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V2][Papay][Direct][OnlyContractPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->mergePayload([
                'appid' => $config[self::getWechatTypeKey($params)] ?? '',
                'mch_id' => $config['mch_id'] ?? '',
                'notify_url' => $payload?->get('notify_url') ?? $config['notify_url'] ?? '',
                'timestamp' => time(),
            ]);

        Logger::info('[Wechat][V2][Papay][Direct][OnlyContractPlugin] 插件装载完毕', ['rocket' => $rocket]);

        /** @var Rocket $rocket */
        $rocket = $next($rocket);

        $rocket->setDestination($rocket->getPayload());

        return $rocket;
    }
}
