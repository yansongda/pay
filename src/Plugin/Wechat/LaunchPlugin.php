<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Logger;
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
        Logger::info('[wechat][LaunchPlugin] 插件开始装载', ['rocket' => $rocket]);

        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        if (should_do_http_request($rocket)) {
            $this->verifySign($rocket);

            $rocket->setDestination($this->formatResponse($rocket));
        }

        Logger::info('[wechat][LaunchPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
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
        $response = $rocket->getDestinationOrigin();

        $timestamp = $response->getHeaderLine('Wechatpay-Timestamp');
        $random = $response->getHeaderLine('Wechatpay-Nonce');
        $sign = $response->getHeaderLine('Wechatpay-Signature');
        $body = $response->getBody()->getContents();

        if (empty($sign)) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_SIGN, '', ['headers' => $response->getHeaders(), 'body' => $body]);
        }

        $content = $timestamp.'\n'.$random.'\n'.$body.'\n';

        if (!$this->verifyResponse($rocket->getParams(), $content, base64_decode($sign))) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_SIGN, '', ['headers' => $response->getHeaders(), 'body' => $body]);
        }
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    protected function verifyResponse(array $params, string $contents, string $sign): bool
    {
        $public = get_wechat_config($params)->get('wechat_public_cert_path');

        if (is_null($public)) {
            throw new InvalidConfigException(InvalidConfigException::WECHAT_CONFIG_ERROR, 'Missing Wechat Config -- [wechat_public_cert_path]');
        }

        return 1 === openssl_verify(
                $contents,
                $sign,
                get_public_crt_or_private_cert($public),
                'sha256WithRSAEncryption');
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    protected function formatResponse(Rocket $rocket): Collection
    {
        $response = $rocket->getDestination();

        $code = $response->get('code');

        if (!is_null($code) && 0 != $code) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_CODE);
        }

        return $response;
    }
}
