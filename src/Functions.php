<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use JetBrains\PhpStorm\Deprecated;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\WechatPublicCertsPlugin;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Douyin;
use Yansongda\Pay\Provider\Jsb;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\get_radar_body;
use function Yansongda\Artful\get_radar_method;

function get_tenant(array $params = []): string
{
    return strval($params['_config'] ?? 'default');
}

function get_public_cert(string $key): string
{
    return is_file($key) ? file_get_contents($key) : $key;
}

function get_private_cert(string $key): string
{
    if (is_file($key)) {
        return file_get_contents($key);
    }

    return "-----BEGIN RSA PRIVATE KEY-----\n".
        wordwrap($key, 64, "\n", true).
        "\n-----END RSA PRIVATE KEY-----";
}

function get_radar_url(array $config, ?Collection $payload): ?string
{
    return match ($config['mode'] ?? Pay::MODE_NORMAL) {
        Pay::MODE_SERVICE => $payload?->get('_service_url') ?? $payload?->get('_url') ?? null,
        Pay::MODE_SANDBOX => $payload?->get('_sandbox_url') ?? $payload?->get('_url') ?? null,
        default => $payload?->get('_url') ?? null,
    };
}

/**
 * @throws ContainerException
 * @throws ServiceNotFoundException
 */
function get_provider_config(string $provider, array $params = []): array
{
    /** @var ConfigInterface $config */
    $config = Pay::get(ConfigInterface::class);

    return $config->get($provider, [])[get_tenant($params)] ?? [];
}

/**
 * @throws ContainerException
 * @throws ServiceNotFoundException
 */
#[Deprecated(reason: '自 v3.7.5 开始废弃', replacement: 'get_provider_config')]
function get_alipay_config(array $params = []): array
{
    $alipay = Pay::get(ConfigInterface::class)->get('alipay');

    return $alipay[get_tenant($params)] ?? [];
}

function get_alipay_url(array $config, ?Collection $payload): string
{
    $url = get_radar_url($config, $payload) ?? '';

    if (str_starts_with($url, 'http')) {
        return $url;
    }

    return Alipay::URL[$config['mode'] ?? Pay::MODE_NORMAL];
}

/**
 * @throws InvalidConfigException
 * @throws InvalidSignException
 */
function verify_alipay_sign(array $config, string $contents, string $sign): void
{
    if (empty($sign)) {
        throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 验证支付宝签名失败-支付宝签名为空', func_get_args());
    }

    $public = $config['alipay_public_cert_path'] ?? null;

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
#[Deprecated(reason: '自 v3.7.5 开始废弃', replacement: 'get_provider_config')]
function get_wechat_config(array $params = []): array
{
    $wechat = Pay::get(ConfigInterface::class)->get('wechat');

    return $wechat[get_tenant($params)] ?? [];
}

function get_wechat_method(?Collection $payload): string
{
    return get_radar_method($payload) ?? 'POST';
}

/**
 * @throws InvalidParamsException
 */
function get_wechat_url(array $config, ?Collection $payload): string
{
    $url = get_radar_url($config, $payload);

    if (empty($url)) {
        throw new InvalidParamsException(Exception::PARAMS_WECHAT_URL_MISSING, '参数异常: 微信 `_url` 或 `_service_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
    }

    if (str_starts_with($url, 'http')) {
        return $url;
    }

    return Wechat::URL[$config['mode'] ?? Pay::MODE_NORMAL].$url;
}

/**
 * @throws InvalidParamsException
 */
function get_wechat_body(?Collection $payload): mixed
{
    $body = get_radar_body($payload);

    if (is_null($body)) {
        throw new InvalidParamsException(Exception::PARAMS_WECHAT_BODY_MISSING, '参数异常: 微信 `_body` 参数缺失：你可能用错插件顺序，应该先使用 `AddPayloadBodyPlugin`');
    }

    return $body;
}

function get_wechat_type_key(array $params): string
{
    $key = ($params['_type'] ?? 'mp').'_app_id';

    if ('app_app_id' === $key) {
        $key = 'app_id';
    }

    return $key;
}

/**
 * @throws InvalidConfigException
 */
function get_wechat_sign(array $config, string $contents): string
{
    $privateKey = $config['mch_secret_cert'] ?? null;

    if (empty($privateKey)) {
        throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_cert]');
    }

    $privateKey = get_private_cert($privateKey);

    openssl_sign($contents, $sign, $privateKey, 'sha256WithRSAEncryption');

    return base64_encode($sign);
}

/**
 * @throws InvalidConfigException
 */
function get_wechat_sign_v2(array $config, array $payload, bool $upper = true): string
{
    $key = $config['mch_secret_key_v2'] ?? null;

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
    $public = get_provider_config('wechat', $params)['wechat_public_cert_path'][$wechatSerial] ?? null;

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

/**
 * @throws InvalidConfigException
 * @throws InvalidSignException
 */
function verify_wechat_sign_v2(array $config, array $destination): void
{
    $sign = $destination['sign'] ?? null;

    if (empty($sign)) {
        throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 微信签名为空', $destination);
    }

    $key = $config['mch_secret_key_v2'] ?? null;

    if (empty($key)) {
        throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_key_v2]');
    }

    if (get_wechat_sign_v2($config, $destination) !== $sign) {
        throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证微信签名失败', $destination);
    }
}

function encrypt_wechat_contents(string $contents, string $publicKey): ?string
{
    if (openssl_public_encrypt($contents, $encrypted, get_public_cert($publicKey), OPENSSL_PKCS1_OAEP_PADDING)) {
        return base64_encode($encrypted);
    }

    return null;
}

function decrypt_wechat_contents(string $encrypted, array $config): ?string
{
    if (openssl_private_decrypt(base64_decode($encrypted), $decrypted, get_private_cert($config['mch_secret_cert'] ?? ''), OPENSSL_PKCS1_OAEP_PADDING)) {
        return $decrypted;
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
        [StartPlugin::class, WechatPublicCertsPlugin::class, AddPayloadBodyPlugin::class, AddPayloadSignaturePlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class],
        $params
    )->get('data', []);

    $wechatConfig = get_provider_config('wechat', $params);

    foreach ($data as $item) {
        $certs[$item['serial_no']] = decrypt_wechat_resource($item['encrypt_certificate'], $wechatConfig)['ciphertext'] ?? '';
    }

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

    $config = get_provider_config('wechat', $params);

    if (empty($path)) {
        var_dump($config['wechat_public_cert_path']);

        return;
    }

    foreach ($config['wechat_public_cert_path'] as $serialNo => $cert) {
        file_put_contents($path.'/'.$serialNo.'.crt', $cert);
    }
}

/**
 * @throws InvalidConfigException
 * @throws DecryptException
 */
function decrypt_wechat_resource(array $resource, array $config): array
{
    $ciphertext = base64_decode($resource['ciphertext'] ?? '');
    $secret = $config['mch_secret_key'] ?? null;

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

    if (false === $decrypted) {
        throw new DecryptException(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID, '加解密异常: 解密失败，请检查微信 mch_secret_key 是否正确');
    }

    if ('certificate' !== $associatedData) {
        $decrypted = json_decode($decrypted, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new DecryptException(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID, '加解密异常: 待解密数据非正常数据');
        }
    }

    return $decrypted;
}

/**
 * @throws ContainerException
 * @throws DecryptException
 * @throws InvalidConfigException
 * @throws InvalidParamsException
 * @throws ServiceNotFoundException
 */
function get_wechat_serial_no(array $params): string
{
    if (!empty($params['_serial_no'])) {
        return $params['_serial_no'];
    }

    $config = get_provider_config('wechat', $params);

    if (empty($config['wechat_public_cert_path'])) {
        reload_wechat_public_certs($params);

        $config = get_provider_config('wechat', $params);
    }

    mt_srand();

    return strval(array_rand($config['wechat_public_cert_path']));
}

/**
 * @throws InvalidParamsException
 */
function get_wechat_public_key(array $config, string $serialNo): string
{
    $publicKey = $config['wechat_public_cert_path'][$serialNo] ?? null;

    if (empty($publicKey)) {
        throw new InvalidParamsException(Exception::PARAMS_WECHAT_SERIAL_NOT_FOUND, '参数异常: 微信公钥序列号为找到 -'.$serialNo);
    }

    return $publicKey;
}

/**
 * @throws InvalidConfigException
 */
function get_wechat_miniprogram_pay_sign(array $config, string $url, string $payload): string
{
    if (empty($config['mini_app_key_virtual_pay'])) {
        throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mini_app_key_virtual_pay]');
    }

    return hash_hmac('sha256', $url.'&'.$payload, $config['mini_app_key_virtual_pay']);
}

function get_wechat_miniprogram_user_sign(string $sessionKey, string $payload): string
{
    return hash_hmac('sha256', $payload, $sessionKey);
}

/**
 * @throws ContainerException
 * @throws ServiceNotFoundException
 */
#[Deprecated(reason: '自 v3.7.5 开始废弃', replacement: 'get_provider_config')]
function get_unipay_config(array $params = []): array
{
    $unipay = Pay::get(ConfigInterface::class)->get('unipay');

    return $unipay[get_tenant($params)] ?? [];
}

/**
 * @throws InvalidConfigException
 * @throws InvalidSignException
 */
function verify_unipay_sign(array $config, string $contents, string $sign, ?string $signPublicKeyCert = null): void
{
    if (empty($sign)) {
        throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 银联签名为空', func_get_args());
    }

    if (empty($signPublicKeyCert) && empty($public = $config['unipay_public_cert_path'] ?? null)) {
        throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常： 缺少银联配置 -- [unipay_public_cert_path]');
    }

    $result = 1 === openssl_verify(
        hash('sha256', $contents),
        base64_decode($sign),
        get_public_cert($signPublicKeyCert ?? $public ?? ''),
        'sha256'
    );

    if (!$result) {
        throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证银联签名失败', func_get_args());
    }
}

/**
 * @throws InvalidParamsException
 */
function get_unipay_url(array $config, ?Collection $payload): string
{
    $url = get_radar_url($config, $payload);

    if (empty($url)) {
        throw new InvalidParamsException(Exception::PARAMS_UNIPAY_URL_MISSING, '参数异常: 银联 `_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
    }

    if (str_starts_with($url, 'http')) {
        return $url;
    }

    return Unipay::URL[$config['mode'] ?? Pay::MODE_NORMAL].$url;
}

/**
 * @throws InvalidParamsException
 */
function get_unipay_body(?Collection $payload): string
{
    $body = get_radar_body($payload);

    if (is_null($body)) {
        throw new InvalidParamsException(Exception::PARAMS_UNIPAY_BODY_MISSING, '参数异常: 银联 `_body` 参数缺失：你可能用错插件顺序，应该先使用 `AddPayloadBodyPlugin`');
    }

    return $body;
}

/**
 * @throws InvalidConfigException
 */
function get_unipay_sign_qra(array $config, array $payload): string
{
    $key = $config['mch_secret_key'] ?? null;

    if (empty($key)) {
        throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常: 缺少银联配置 -- [mch_secret_key]');
    }

    ksort($payload);

    $buff = '';

    foreach ($payload as $k => $v) {
        $buff .= ('sign' != $k && '' != $v && !is_array($v)) ? $k.'='.$v.'&' : '';
    }

    return strtoupper(md5($buff.'key='.$key));
}

/**
 * @throws InvalidConfigException
 * @throws InvalidSignException
 */
function verify_unipay_sign_qra(array $config, array $destination): void
{
    $sign = $destination['sign'] ?? null;

    if (empty($sign)) {
        throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 银联签名为空', $destination);
    }

    $key = $config['mch_secret_key'] ?? null;

    if (empty($key)) {
        throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常: 缺少银联配置 -- [mch_secret_key]');
    }

    if (get_unipay_sign_qra($config, $destination) !== $sign) {
        throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证银联签名失败', $destination);
    }
}

function get_jsb_url(array $config, ?Collection $payload): string
{
    $url = get_radar_url($config, $payload) ?? '';

    if (str_starts_with($url, 'http')) {
        return $url;
    }

    return Jsb::URL[$config['mode'] ?? Pay::MODE_NORMAL];
}

/**
 * @throws InvalidConfigException
 * @throws InvalidSignException
 */
function verify_jsb_sign(array $config, string $content, string $sign): void
{
    if (empty($sign)) {
        throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 江苏银行签名为空', func_get_args());
    }

    $publicCert = $config['jsb_public_cert_path'] ?? null;

    if (empty($publicCert)) {
        throw new InvalidConfigException(Exception::CONFIG_JSB_INVALID, '配置异常: 缺少配置参数 -- [jsb_public_cert_path]');
    }

    $result = 1 === openssl_verify(
        $content,
        base64_decode($sign),
        get_public_cert($publicCert)
    );

    if (!$result) {
        throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证江苏银行签名失败', func_get_args());
    }
}

/**
 * @throws InvalidParamsException
 */
function get_douyin_url(array $config, ?Collection $payload): string
{
    $url = get_radar_url($config, $payload);

    if (empty($url)) {
        throw new InvalidParamsException(Exception::PARAMS_DOUYIN_URL_MISSING, '参数异常: 抖音 `_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
    }

    if (str_starts_with($url, 'http')) {
        return $url;
    }

    return Douyin::URL[$config['mode'] ?? Pay::MODE_NORMAL].$url;
}
