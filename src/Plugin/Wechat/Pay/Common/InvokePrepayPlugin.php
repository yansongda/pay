<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class InvokePrepayPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Exception
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[wechat][InvokePrepayPlugin] 插件开始装载', ['rocket' => $rocket]);

        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        $prepayId = $rocket->getPayload()->get('prepay_id');

        if (is_null($prepayId)) {
            throw new InvalidResponseException(InvalidResponseException::RESPONSE_MISSING_NECESSARY_PARAMS);
        }

        $rocket->setPayload(new Collection([
            'appid' => $this->getAppid($rocket),
            'timeStamp' => time().'',
            'nonceStr' => Str::random(32),
            'package' => 'prepay_id='.$prepayId,
            'signType' => 'RSA',
        ]));

        $rocket->mergePayload([
            'sign' => $this->getSign($rocket),
        ]);

        Logger::info('[wechat][InvokePrepayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getSign(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        $contents = $payload->get('appid', '').'\n'.
            $payload->get('timeStamp', '').'\n'.
            $payload->get('nonceStr', '').'\n'.
            $payload->get('package', '').'\n';

        return get_wechat_sign($rocket->getParams(), $contents);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getAppid(Rocket $rocket): string
    {
        $config = get_wechat_config($rocket->getParams());

        return $config->get('mp_app_id', '');
    }
}
