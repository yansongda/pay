<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Direction\NoHttpRequestDirection;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter3_3.shtml
 */
class OnlyContractPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V2][Papay][Direct][OnlyContractPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();

        $rocket->setPacker(NoHttpRequestDirection::class)
            ->mergePayload([
                'appid' => $config[get_wechat_type_key($params)] ?? '',
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
