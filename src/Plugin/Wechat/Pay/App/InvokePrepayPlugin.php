<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\App;

use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

class InvokePrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Exception
     */
    protected function getInvokeConfig(Rocket $rocket, string $prepayId): Config
    {
        $config = new Config([
            'appid' => $this->getAppid($rocket),
            'partnerid' => get_wechat_config($rocket->getParams())->get('mch_id'),
            'prepayid' => $prepayId,
            'package' => 'Sign=WXPay',
            'noncestr' => Str::random(32),
            'timestamp' => time().'',
        ]);

        $config->set('sign', $this->getSign($config, $rocket->getParams()));

        return $config;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getSign(Collection $invokeConfig, array $params): string
    {
        $contents = $invokeConfig->get('appid', '')."\n".
            $invokeConfig->get('timestamp', '')."\n".
            $invokeConfig->get('noncestr', '')."\n".
            $invokeConfig->get('prepayid', '')."\n";

        return get_wechat_sign($params, $contents);
    }

    protected function getAppid(Rocket $rocket): string
    {
        $config = get_wechat_config($rocket->getParams());

        return $config->get('app_id', '');
    }
}
