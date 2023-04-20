<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Papay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Direction\NoHttpRequestDirection;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_sign_v2;

/**
 * 返回只签约（委托代扣）参数.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter3_3.shtml
 */
class OnlyContractPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $config = get_wechat_config($rocket->getParams());
        $wechatId = $this->getWechatId($config, $rocket->getParams());

        if (!$rocket->getPayload()->has('notify_url')) {
            $wechatId['notify_url'] = $config['notify_url'] ?? '';
        }

        $rocket->mergePayload($wechatId);
        $rocket->mergePayload([
            'sign' => get_wechat_sign_v2($rocket->getParams(), $rocket->getPayload()->all()),
        ]);

        $rocket->setDestination($rocket->getPayload());
        $rocket->setDirection(NoHttpRequestDirection::class);

        return $next($rocket);
    }

    protected function getWechatId(array $config, array $params): array
    {
        $configKey = $this->getConfigKey($params);

        return [
            'appid' => $config[$configKey] ?? '',
            'mch_id' => $config['mch_id'] ?? '',
        ];
    }

    protected function getConfigKey(array $params): string
    {
        $key = ($params['_type'] ?? 'mp').'_app_id';

        if ('app_app_id' === $key) {
            $key = 'app_id';
        }

        return $key;
    }
}
