<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Pos;

use function Yansongda\Pay\get_wechat_config;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_10&index=1
 */
class PayPlugin extends GeneralPlugin
{
    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/xml',
            'User-Agent' => 'yansongda/pay-v3',
        ];
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());
        $configKey = $this->getConfigKey($rocket->getParams());

        $rocket->mergeParams(['_version' => 'v2']);

        $rocket->mergePayload([
            'appid' => $config[$configKey] ?? '',
            'mch_id' => $config['mch_id'] ?? '',
        ]);
    }

    protected function getUri(Rocket $rocket): string
    {
        return 'pay/micropay';
    }
}
