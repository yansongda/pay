<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class CallbackPlugin implements PluginInterface
{
    protected const AUTH_TAG_LENGTH_BYTE = 16;

    protected const KEY_LENGTH_BYTE = 32;

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[wechat][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->assertRequestAndParams($rocket);

        verify_wechat_sign($rocket->getDestinationOrigin(), $rocket->getParams());

        $body = json_decode($rocket->getDestination()->getBody()->getContents(), true);
        $destination = $this->decrypt($body['resource'] ?? [], $rocket->getParams());

        $rocket->setDirection(NoHttpRequestParser::class)
            ->setPayload($body)
            ->setDestination(new Collection($destination));

        Logger::info('[wechat][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function assertRequestAndParams(Rocket $rocket): void
    {
        $request = $rocket->getParams()['request'] ?? null;

        if (is_null($request) || !($request instanceof ServerRequestInterface)) {
            throw new InvalidParamsException(InvalidParamsException::REQUEST_NULL_ERROR);
        }

        $rocket->setDestinationOrigin($request);
        $rocket->setDestination($request);
        $rocket->setParams($rocket->getParams()['params'] ?? []);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    protected function decrypt(array $resource, array $params): array
    {
        $ciphertext = base64_decode($resource['ciphertext']);
        $secret = get_wechat_config($params)->get('mch_secret_key');

        if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_REQUEST_PARAMS);
        }

        if (is_null($secret) || self::KEY_LENGTH_BYTE != strlen($secret)) {
            throw new InvalidConfigException(InvalidConfigException::WECHAT_CONFIG_ERROR, 'Missing Wechat Config -- [mch_secret_key]');
        }

        $decrypted = json_decode(openssl_decrypt(
            substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE),
            'aes-256-gcm',
            $secret,
            OPENSSL_RAW_DATA,
            $resource['nonce'] ?? '',
            substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE),
            $resource['associated_data'] ?? ''
        ), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_REQUEST_ENCRYPTED_DATA);
        }

        return $decrypted;
    }
}
