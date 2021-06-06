<?php

declare(strict_types=1);

use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

if (!function_exists('get_alipay_config')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    function get_alipay_config(array $params): Config
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

if (!function_exists('verify_alipay_response')) {
    /**
     * @param string $sign base64decode 之后的
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    function verify_alipay_response(array $params, string $contents, string $sign): bool
    {
        $public = get_alipay_config($params)->get('alipay_public_cert_path');

        if (is_null($public)) {
            throw new InvalidConfigException(InvalidConfigException::ALIPAY_CONFIG_ERROR, 'Missing Alipay Config -- [alipay_public_cert_path]');
        }

        return 1 === openssl_verify(
            $contents,
            $sign,
            get_public_crt_or_private_cert($public),
            OPENSSL_ALGO_SHA256);
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
