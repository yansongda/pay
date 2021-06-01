<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class SignPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $payload = $rocket->getPayload();
        $privateKey = $this->getPrivateKey($rocket->getParams());

        openssl_sign($this->getSignContent($payload), $sign, $privateKey, OPENSSL_ALGO_SHA256);

        $sign = base64_encode($sign);

        Logger::debug('支付宝支付生成签名', ['params' => $payload, 'sign' => $sign]);

        !is_resource($privateKey) ?: openssl_free_key($privateKey);

        return $next($rocket->mergePayload([
            'sign' => $sign,
        ]));
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return false|resource|string
     */
    protected function getPrivateKey(array $params)
    {
        $privateKey = get_alipay_config($params)->get('private_key');

        if (is_null($privateKey)) {
            throw new InvalidConfigException(InvalidConfigException::ALIPAY_PRIVATE_KEY_ERROR, 'Missing Alipay Config -- [private_key]');
        }

        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".
            wordwrap($privateKey, 64, "\n", true).
            "\n-----END RSA PRIVATE KEY-----";

        if (Str::endsWith($privateKey, '.pem')) {
            $privateKey = openssl_pkey_get_private(
                Str::startsWith($privateKey, 'file://') ? $privateKey : 'file://'.$privateKey
            );
        }

        return $privateKey;
    }

    protected function getSignContent(Collection $payload): string
    {
        $payload = $payload->sortKeys();

        return Arr::query($payload->all());
    }
}
