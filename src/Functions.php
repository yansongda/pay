<?php

declare(strict_types=1);

use Psr\Http\Message\MessageInterface;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

if (!function_exists('should_do_http_request')) {
    function should_do_http_request(Rocket $rocket): bool
    {
        $direction = $rocket->getDirection();

        return is_null($direction) ||
            (NoHttpRequestParser::class !== $direction &&
            !in_array(NoHttpRequestParser::class, class_parents($direction)));
    }
}

if (!function_exists('get_alipay_config')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    function get_alipay_config(array $params = []): Config
    {
        $alipay = Pay::get(ConfigInterface::class)->get('alipay');

        $config = $params['_config'] ?? 'default';

        return new Config($alipay[$config] ?? []);
    }
}

if (!function_exists('get_public_crt_or_private_cert')) {
    /**
     * @return false|resource|string
     */
    function get_public_crt_or_private_cert(string $key)
    {
        if (Str::endsWith($key, '.crt')) {
            $key = file_get_contents($key);
        } elseif (Str::endsWith($key, '.pem')) {
            $key = openssl_pkey_get_private(
                Str::startsWith($key, 'file://') ? $key : 'file://'.$key
            );
        } else {
            $key = "-----BEGIN RSA PRIVATE KEY-----\n".
                wordwrap($key, 64, "\n", true).
                "\n-----END RSA PRIVATE KEY-----";
        }

        return $key;
    }
}

if (!function_exists('verify_alipay_sign')) {
    /**
     * @param string $sign base64decode 之后的
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    function verify_alipay_sign(array $params, string $contents, string $sign): void
    {
        $public = get_alipay_config($params)->get('alipay_public_cert_path');

        if (is_null($public)) {
            throw new InvalidConfigException(InvalidConfigException::ALIPAY_CONFIG_ERROR, 'Missing Alipay Config -- [alipay_public_cert_path]');
        }

        $result = 1 === openssl_verify(
            $contents,
            $sign,
            get_public_crt_or_private_cert($public),
            OPENSSL_ALGO_SHA256);

        if (!$result) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_SIGN, '', func_get_args());
        }
    }
}

if (!function_exists('get_wechat_config')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    function get_wechat_config(array $params): Config
    {
        $wechat = Pay::get(ConfigInterface::class)->get('wechat');

        $config = $params['_config'] ?? 'default';

        return new Config($wechat[$config] ?? []);
    }
}

if (!function_exists('get_wechat_sign')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    function get_wechat_sign(array $params, string $contents): string
    {
        $privateKey = get_wechat_config($params)->get('mch_secret_cert');

        if (is_null($privateKey)) {
            throw new InvalidConfigException(InvalidConfigException::WECHAT_CONFIG_ERROR, 'Missing Wechat Config -- [app_secret_cert]');
        }

        $privateKey = get_public_crt_or_private_cert($privateKey);

        openssl_sign($contents, $sign, $privateKey, 'sha256WithRSAEncryption');

        $sign = base64_encode($sign);

        !is_resource($privateKey) ?: openssl_free_key($privateKey);

        return $sign;
    }
}

if (!function_exists('verify_wechat_sign')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    function verify_wechat_sign(MessageInterface $message, array $params): void
    {
        $timestamp = $message->getHeaderLine('Wechatpay-Timestamp');
        $random = $message->getHeaderLine('Wechatpay-Nonce');
        $body = $message->getBody()->getContents();
        $content = $timestamp.'\n'.$random.'\n'.$body.'\n';

        $sign = $message->getHeaderLine('Wechatpay-Signature');
        $public = get_wechat_config($params)->get('wechat_public_cert_path');

        if (empty($sign)) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_SIGN, '', ['headers' => $message->getHeaders(), 'body' => $body]);
        }

        if (is_null($public)) {
            throw new InvalidConfigException(InvalidConfigException::WECHAT_CONFIG_ERROR, 'Missing Wechat Config -- [wechat_public_cert_path]');
        }

        $result = 1 === openssl_verify(
            $content,
            base64_decode($sign),
            get_public_crt_or_private_cert($public),
            'sha256WithRSAEncryption'
        );

        if (!$result) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_SIGN, '', ['headers' => $message->getHeaders(), 'body' => $body]);
        }
    }
}
