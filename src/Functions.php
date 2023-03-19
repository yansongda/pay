<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\PreparePlugin;
use Yansongda\Pay\Plugin\Wechat\RadarSignPlugin;
use Yansongda\Pay\Plugin\Wechat\WechatPublicCertsPlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Supports\Str;

if (!function_exists('should_do_http_request')) {
    function should_do_http_request(?string $direction): bool
    {
        return is_null($direction) ||
            (NoHttpRequestParser::class !== $direction &&
            !in_array(NoHttpRequestParser::class, class_parents($direction)));
    }
}

if (!function_exists('get_tenant')) {
    function get_tenant(array $params = []): string
    {
        return strval($params['_config'] ?? 'default');
    }
}

if (!function_exists('get_alipay_config')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    function get_alipay_config(array $params = []): array
    {
        $alipay = Pay::get(ConfigInterface::class)->get('alipay');

        return $alipay[get_tenant($params)] ?? [];
    }
}

if (!function_exists('get_public_cert')) {
    function get_public_cert(string $key): string
    {
        return Str::endsWith($key, ['.cer', '.crt', '.pem']) ? file_get_contents($key) : $key;
    }
}

if (!function_exists('get_private_cert')) {
    function get_private_cert(string $key): string
    {
        if (Str::endsWith($key, ['.crt', '.pem'])) {
            return file_get_contents($key);
        }

        return "-----BEGIN RSA PRIVATE KEY-----\n".
            wordwrap($key, 64, "\n", true).
            "\n-----END RSA PRIVATE KEY-----";
    }
}

if (!function_exists('verify_alipay_sign')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    function verify_alipay_sign(array $params, string $contents, string $sign): void
    {
        $public = get_alipay_config($params)['alipay_public_cert_path'] ?? null;

        if (empty($public)) {
            throw new InvalidConfigException(Exception::ALIPAY_CONFIG_ERROR, 'Missing Alipay Config -- [alipay_public_cert_path]');
        }

        $result = 1 === openssl_verify(
            $contents,
            base64_decode($sign),
            get_public_cert($public),
            OPENSSL_ALGO_SHA256);

        if (!$result) {
            throw new InvalidResponseException(Exception::INVALID_RESPONSE_SIGN, 'Verify Alipay Response Sign Failed', func_get_args());
        }
    }
}

if (!function_exists('get_wechat_config')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    function get_wechat_config(array $params): array
    {
        $wechat = Pay::get(ConfigInterface::class)->get('wechat');

        return $wechat[get_tenant($params)] ?? [];
    }
}

if (!function_exists('get_wechat_base_uri')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    function get_wechat_base_uri(array $params): string
    {
        $config = get_wechat_config($params);

        return Wechat::URL[$config['mode'] ?? Pay::MODE_NORMAL];
    }
}

if (!function_exists('get_wechat_sign')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    function get_wechat_sign(array $params, string $contents): string
    {
        $privateKey = get_wechat_config($params)['mch_secret_cert'] ?? null;

        if (empty($privateKey)) {
            throw new InvalidConfigException(Exception::WECHAT_CONFIG_ERROR, 'Missing Wechat Config -- [mch_secret_cert]');
        }

        $privateKey = get_private_cert($privateKey);

        openssl_sign($contents, $sign, $privateKey, 'sha256WithRSAEncryption');

        return base64_encode($sign);
    }
}

if (!function_exists('verify_wechat_sign')) {
    /**
     * @param \Psr\Http\Message\ServerRequestInterface|\Psr\Http\Message\ResponseInterface $message
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    function verify_wechat_sign(MessageInterface $message, array $params): void
    {
        if ($message instanceof ServerRequestInterface && 'localhost' === $message->getUri()->getHost()) {
            return;
        }

        $wechatSerial = $message->getHeaderLine('Wechatpay-Serial');
        $timestamp = $message->getHeaderLine('Wechatpay-Timestamp');
        $random = $message->getHeaderLine('Wechatpay-Nonce');
        $sign = $message->getHeaderLine('Wechatpay-Signature');
        $body = (string) $message->getBody();

        $content = $timestamp."\n".$random."\n".$body."\n";
        $public = get_wechat_config($params)['wechat_public_cert_path'][$wechatSerial] ?? null;

        if (empty($sign)) {
            throw new InvalidResponseException(Exception::INVALID_RESPONSE_SIGN, '', ['headers' => $message->getHeaders(), 'body' => $body]);
        }

        $public = get_public_cert(
            empty($public) ? reload_wechat_public_certs($params, $wechatSerial) : $public
        );

        $result = 1 === openssl_verify(
            $content,
            base64_decode($sign),
            $public,
            'sha256WithRSAEncryption'
        );

        if (!$result) {
            throw new InvalidResponseException(Exception::INVALID_RESPONSE_SIGN, '', ['headers' => $message->getHeaders(), 'body' => $body]);
        }
    }
}

if (!function_exists('encrypt_wechat_contents')) {
    function encrypt_wechat_contents(string $contents, string $publicKey): ?string
    {
        if (openssl_public_encrypt($contents, $encrypted, get_public_cert($publicKey), OPENSSL_PKCS1_OAEP_PADDING)) {
            return base64_encode($encrypted);
        }

        return null;
    }
}

if (!function_exists('reload_wechat_public_certs')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    function reload_wechat_public_certs(array $params, ?string $serialNo = null): string
    {
        $data = Pay::wechat()->pay(
            [PreparePlugin::class, WechatPublicCertsPlugin::class, RadarSignPlugin::class, ParserPlugin::class],
            $params
        )->get('data', []);

        foreach ($data as $item) {
            $certs[$item['serial_no']] = decrypt_wechat_resource($item['encrypt_certificate'], $params)['ciphertext'] ?? '';
        }

        $wechatConfig = get_wechat_config($params);

        Pay::get(ConfigInterface::class)->set(
            'wechat.'.get_tenant($params).'.wechat_public_cert_path',
            ((array) ($wechatConfig['wechat_public_cert_path'] ?? [])) + ($certs ?? []),
        );

        if (!is_null($serialNo) && empty($certs[$serialNo])) {
            throw new InvalidConfigException(Exception::WECHAT_CONFIG_ERROR, 'Get Wechat Public Cert Error');
        }

        return $certs[$serialNo] ?? '';
    }
}

if (!function_exists('get_wechat_public_certs')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    function get_wechat_public_certs(array $params = [], ?string $path = null): void
    {
        reload_wechat_public_certs($params);

        $config = get_wechat_config($params);

        if (empty($path)) {
            var_dump($config['wechat_public_cert_path']);

            return;
        }

        foreach ($config['wechat_public_cert_path'] as $serialNo => $cert) {
            file_put_contents($path.'/'.$serialNo.'.crt', $cert);
        }
    }
}

if (!function_exists('decrypt_wechat_resource')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    function decrypt_wechat_resource(array $resource, array $params): array
    {
        $ciphertext = base64_decode($resource['ciphertext'] ?? '');
        $secret = get_wechat_config($params)['mch_secret_key'] ?? null;

        if (strlen($ciphertext) <= Wechat::AUTH_TAG_LENGTH_BYTE) {
            throw new InvalidResponseException(Exception::INVALID_CIPHERTEXT_PARAMS);
        }

        if (is_null($secret) || Wechat::MCH_SECRET_KEY_LENGTH_BYTE != strlen($secret)) {
            throw new InvalidConfigException(Exception::WECHAT_CONFIG_ERROR, 'Missing Wechat Config -- [mch_secret_key]');
        }

        switch ($resource['algorithm'] ?? '') {
            case 'AEAD_AES_256_GCM':
                $resource['ciphertext'] = decrypt_wechat_resource_aes_256_gcm($ciphertext, $secret, $resource['nonce'] ?? '', $resource['associated_data'] ?? '');
                break;
            default:
                throw new InvalidResponseException(Exception::INVALID_REQUEST_ENCRYPTED_METHOD);
        }

        return $resource;
    }
}

if (!function_exists('decrypt_wechat_resource_aes_256_gcm')) {
    /**
     * @return array|string
     *
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    function decrypt_wechat_resource_aes_256_gcm(string $ciphertext, string $secret, string $nonce, string $associatedData)
    {
        $decrypted = openssl_decrypt(
            substr($ciphertext, 0, -Wechat::AUTH_TAG_LENGTH_BYTE),
            'aes-256-gcm',
            $secret,
            OPENSSL_RAW_DATA,
            $nonce,
            substr($ciphertext, -Wechat::AUTH_TAG_LENGTH_BYTE),
            $associatedData
        );

        if ('certificate' !== $associatedData) {
            $decrypted = json_decode(strval($decrypted), true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new InvalidResponseException(Exception::INVALID_REQUEST_ENCRYPTED_DATA);
            }
        }

        return $decrypted;
    }
}

if (!function_exists('get_unipay_config')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    function get_unipay_config(array $params): array
    {
        $unipay = Pay::get(ConfigInterface::class)->get('unipay');

        return $unipay[get_tenant($params)] ?? [];
    }
}

if (!function_exists('verify_unipay_sign')) {
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    function verify_unipay_sign(array $params, string $contents, string $sign): void
    {
        if (empty($params['signPubKeyCert'])
            && empty($public = get_unipay_config($params)['unipay_public_cert_path'] ?? null)) {
            throw new InvalidConfigException(Exception::UNIPAY_CONFIG_ERROR, 'Missing Unipay Config -- [unipay_public_cert_path]');
        }

        $result = 1 === openssl_verify(
            hash('sha256', $contents),
            base64_decode($sign),
            get_public_cert($params['signPubKeyCert'] ?? $public ?? ''),
            'sha256');

        if (!$result) {
            throw new InvalidResponseException(Exception::INVALID_RESPONSE_SIGN, 'Verify Unipay Response Sign Failed', func_get_args());
        }
    }
}
