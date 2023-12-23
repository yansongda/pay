<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Direction\NoHttpRequestDirection;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\PreparePlugin;
use Yansongda\Pay\Plugin\Wechat\RadarSignPlugin;
use Yansongda\Pay\Plugin\Wechat\WechatPublicCertsPlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

function should_do_http_request(string $direction): bool
{
    return NoHttpRequestDirection::class !== $direction
        && !in_array(NoHttpRequestDirection::class, class_parents($direction));
}

function get_tenant(array $params = []): string
{
    return strval($params['_config'] ?? 'default');
}

/**
 * @throws InvalidConfigException
 */
function get_direction(mixed $direction): DirectionInterface
{
    try {
        $direction = Pay::get($direction);

        $direction = is_string($direction) ? Pay::get($direction) : $direction;
    } catch (ContainerException|ServiceNotFoundException) {
    }

    if (!$direction instanceof DirectionInterface) {
        throw new InvalidConfigException(Exception::CONFIG_DIRECTION_INVALID, '配置异常: 配置的 DirectionInterface 未实现 `DirectionInterface`');
    }

    return $direction;
}

function get_public_cert(string $key): string
{
    return Str::endsWith($key, ['.cer', '.crt', '.pem']) ? file_get_contents($key) : $key;
}

function get_private_cert(string $key): string
{
    if (Str::endsWith($key, ['.crt', '.pem'])) {
        return file_get_contents($key);
    }

    return "-----BEGIN RSA PRIVATE KEY-----\n".
        wordwrap($key, 64, "\n", true).
        "\n-----END RSA PRIVATE KEY-----";
}

function filter_params(array $params, ?Closure $closure = null): array
{
    return array_filter($params, static fn ($v, $k) => !Str::startsWith($k, '_') && (empty($closure) || $closure($k, $v)), ARRAY_FILTER_USE_BOTH);
}

/**
 * @throws ContainerException
 * @throws ServiceNotFoundException
 */
function get_alipay_config(array $params = []): array
{
    $alipay = Pay::get(ConfigInterface::class)->get('alipay');

    return $alipay[get_tenant($params)] ?? [];
}

/**
 * @throws ContainerException
 * @throws InvalidConfigException
 * @throws ServiceNotFoundException
 * @throws InvalidSignException
 */
function verify_alipay_sign(array $params, string $contents, string $sign): void
{
    $public = get_alipay_config($params)['alipay_public_cert_path'] ?? null;

    if (empty($public)) {
        throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [alipay_public_cert_path]');
    }

    $result = 1 === openssl_verify(
        $contents,
        base64_decode($sign),
        get_public_cert($public),
        OPENSSL_ALGO_SHA256
    );

    if (!$result) {
        throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证支付宝签名失败', func_get_args());
    }
}

/**
 * @throws ContainerException
 * @throws ServiceNotFoundException
 */
function get_wechat_config(array $params): array
{
    $wechat = Pay::get(ConfigInterface::class)->get('wechat');

    return $wechat[get_tenant($params)] ?? [];
}

function get_wechat_method(?Collection $payload): string
{
    return strtoupper($payload?->get('_method') ?? 'POST');
}

/**
 * @throws ContainerException
 * @throws InvalidParamsException
 * @throws ServiceNotFoundException
 */
function get_wechat_url(array $params, ?Collection $payload): string
{
    $url = $payload?->get('_url') ?? '';

    if (empty($url)) {
        throw new InvalidParamsException(Exception::PARAMS_WECHAT_URL_MISSING, '参数异常: 微信 `_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
    }

    if (str_starts_with($url, 'http')) {
        return $url;
    }

    $config = get_wechat_config($params);

    return Wechat::URL[$config['mode'] ?? Pay::MODE_NORMAL].$url;
}

/**
 * @throws InvalidParamsException
 */
function get_wechat_body(?Collection $payload): string
{
    $body = $payload?->get('_body') ?? '';

    if (empty($body)) {
        throw new InvalidParamsException(Exception::PARAMS_WECHAT_BODY_MISSING, '参数异常: 微信 `_body` 参数缺失：你可能用错插件顺序，应该先使用 `AddPayloadBodyPlugin`');
    }

    return $body;
}

/**
 * @throws ContainerException
 * @throws ServiceNotFoundException
 */
function get_wechat_base_uri(array $params): string
{
    $config = get_wechat_config($params);

    return Wechat::URL[$config['mode'] ?? Pay::MODE_NORMAL];
}

/**
 * @throws ContainerException
 * @throws ServiceNotFoundException
 * @throws InvalidConfigException
 */
function get_wechat_sign(array $params, string $contents): string
{
    $privateKey = get_wechat_config($params)['mch_secret_cert'] ?? null;

    if (empty($privateKey)) {
        throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_cert]');
    }

    $privateKey = get_private_cert($privateKey);

    openssl_sign($contents, $sign, $privateKey, 'sha256WithRSAEncryption');

    return base64_encode($sign);
}

/**
 * @throws ContainerException
 * @throws ServiceNotFoundException
 * @throws InvalidConfigException
 */
function get_wechat_sign_v2(array $params, array $payload, bool $upper = true): string
{
    $key = get_wechat_config($params)['mch_secret_key_v2'] ?? null;

    if (empty($key)) {
        throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_key_v2]');
    }

    ksort($payload);

    $buff = '';

    foreach ($payload as $k => $v) {
        $buff .= ('sign' != $k && '' != $v && !is_array($v)) ? $k.'='.$v.'&' : '';
    }

    $sign = md5($buff.'key='.$key);

    return $upper ? strtoupper($sign) : $sign;
}

/**
 * @throws ContainerException
 * @throws DecryptException
 * @throws InvalidConfigException
 * @throws InvalidParamsException
 * @throws InvalidSignException
 * @throws ServiceNotFoundException
 */
function verify_wechat_sign(ResponseInterface|ServerRequestInterface $message, array $params): void
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
        throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 微信签名为空', ['headers' => $message->getHeaders(), 'body' => $body]);
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
        throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证微信签名失败', ['headers' => $message->getHeaders(), 'body' => $body]);
    }
}

function encrypt_wechat_contents(string $contents, string $publicKey): ?string
{
    if (openssl_public_encrypt($contents, $encrypted, get_public_cert($publicKey), OPENSSL_PKCS1_OAEP_PADDING)) {
        return base64_encode($encrypted);
    }

    return null;
}

/**
 * @throws ContainerException
 * @throws DecryptException
 * @throws InvalidConfigException
 * @throws InvalidParamsException
 * @throws ServiceNotFoundException
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
        throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 获取微信 wechat_public_cert_path 配置失败');
    }

    return $certs[$serialNo] ?? '';
}

/**
 * @throws ContainerException
 * @throws DecryptException
 * @throws InvalidConfigException
 * @throws InvalidParamsException
 * @throws ServiceNotFoundException
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

/**
 * @throws ContainerException
 * @throws InvalidConfigException
 * @throws ServiceNotFoundException
 * @throws DecryptException
 */
function decrypt_wechat_resource(array $resource, array $params): array
{
    $ciphertext = base64_decode($resource['ciphertext'] ?? '');
    $secret = get_wechat_config($params)['mch_secret_key'] ?? null;

    if (strlen($ciphertext) <= Wechat::AUTH_TAG_LENGTH_BYTE) {
        throw new DecryptException(Exception::DECRYPT_WECHAT_CIPHERTEXT_PARAMS_INVALID, '加解密异常: ciphertext 位数过短');
    }

    if (is_null($secret) || Wechat::MCH_SECRET_KEY_LENGTH_BYTE != strlen($secret)) {
        throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_key]');
    }

    $resource['ciphertext'] = match ($resource['algorithm'] ?? '') {
        'AEAD_AES_256_GCM' => decrypt_wechat_resource_aes_256_gcm($ciphertext, $secret, $resource['nonce'] ?? '', $resource['associated_data'] ?? ''),
        default => throw new DecryptException(Exception::DECRYPT_WECHAT_DECRYPTED_METHOD_INVALID, '加解密异常: algorithm 不支持'),
    };

    return $resource;
}

/**
 * @throws DecryptException
 */
function decrypt_wechat_resource_aes_256_gcm(string $ciphertext, string $secret, string $nonce, string $associatedData): array|string
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
            throw new DecryptException(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID, '加解密异常: 待解密数据非正常数据');
        }
    }

    return $decrypted;
}

/**
 * @throws ContainerException
 * @throws ServiceNotFoundException
 */
function get_unipay_config(array $params): array
{
    $unipay = Pay::get(ConfigInterface::class)->get('unipay');

    return $unipay[get_tenant($params)] ?? [];
}

/**
 * @throws ContainerException
 * @throws InvalidConfigException
 * @throws ServiceNotFoundException
 * @throws InvalidSignException
 */
function verify_unipay_sign(array $params, string $contents, string $sign): void
{
    if (empty($params['signPubKeyCert'])
        && empty($public = get_unipay_config($params)['unipay_public_cert_path'] ?? null)) {
        throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常： 缺少银联配置 -- [unipay_public_cert_path]');
    }

    $result = 1 === openssl_verify(
        hash('sha256', $contents),
        base64_decode($sign),
        get_public_cert($params['signPubKeyCert'] ?? $public ?? ''),
        'sha256'
    );

    if (!$result) {
        throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证银联签名失败', func_get_args());
    }
}
