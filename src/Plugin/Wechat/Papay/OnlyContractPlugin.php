<?php

namespace Yansongda\Pay\Plugin\Wechat\Papay;

use Closure;

use function Yansongda\Pay\get_wechat_config;

use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Plugin\Wechat\RadarSignPlugin;
use Yansongda\Pay\Rocket;

/**
 * 返回只签约（委托代扣）参数.
 */
class OnlyContractPlugin extends RadarSignPlugin
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $config = get_wechat_config($rocket->getParams());

        $wechatId = $this->getWechatId($config, $rocket);

        if (!$rocket->getPayload()->has('notify_url')) {
            $wechatId['notify_url'] = $config['notify_url'] ?? null;
        }

        $rocket->mergePayload($wechatId);

        $rocket->mergePayload([
            'sign' => $this->v2GetSign($config['mch_secret_key_v2'] ?? '', $rocket->getPayload()->all()),
        ]);

        $rocket->setDestination($rocket->getPayload());

        $rocket->setDirection(NoHttpRequestParser::class);

        return $next($rocket);
    }

    protected function getWechatId(array $config, Rocket $rocket): array
    {
        $configKey = $this->getConfigKey($rocket->getParams());

        $result = [
            'appid' => $config[$configKey] ?? '',
            'mch_id' => $config['mch_id'] ?? '',
        ];

        return $result;
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
