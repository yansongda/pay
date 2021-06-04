<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class LaunchPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        if ($rocket->getDestination() instanceof ResponseInterface) {
            return $rocket;
        }

        $rocket->setDestination($this->getMethodResponse($rocket));

        return $rocket;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getMethodResponse(Rocket $rocket): Collection
    {
        $response = Collection::wrap(
            $rocket->getDestination()->get($this->getResponseKey($rocket))
        );

        $this->verifySign($rocket);

        if (10000 != $response->get('code')) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_CODE, 'Invalid response code', $response->all());
        }

        return $response;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function verifySign(Rocket $rocket): void
    {
        $response = $rocket->getDestination()->get($this->getResponseKey($rocket));
        $sign = $rocket->getDestination()->get('sign', '');

        if ('' === $sign || is_null($response)) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_SIGN);
        }

        $result = openssl_verify(json_encode($response, JSON_UNESCAPED_UNICODE), base64_decode($sign), $this->getAlipayPublicKey($rocket), OPENSSL_ALGO_SHA256);

        if (1 !== $result) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_SIGN);
        }
    }

    protected function getResponseKey(Rocket $rocket): string
    {
        $method = $rocket->getPayload()->get('method');

        return str_replace('.', '_', $method).'_response';
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    protected function getAlipayPublicKey(Rocket $rocket)
    {
        $public = get_alipay_config($rocket->getParams())->get('alipay_public_cert_path');

        if (is_null($public)) {
            throw new InvalidConfigException(InvalidConfigException::ALIPAY_CONFIG_ERROR, 'Missing Alipay Config -- [alipay_public_cert_path]');
        }

        return get_alipay_cert($public);
    }
}
