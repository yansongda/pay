<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\WechatPublicCertsPlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\get_radar_body;
use function Yansongda\Artful\get_radar_method;

trait WechatTrait
{
    use ProviderConfigTrait;

    public static function getWechatMethod(?Collection $payload): string
    {
        return get_radar_method($payload) ?? 'POST';
    }

    /**
     * @throws InvalidParamsException
     */
    public static function getWechatUrl(WechatConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_URL_MISSING, '参数异常: 微信 `_url` 或 `_service_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
        }

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Wechat::URL[$config->getMode()].$url;
    }

    /**
     * @throws InvalidParamsException
     */
    public static function getWechatBody(?Collection $payload): mixed
    {
        $body = get_radar_body($payload);

        if (is_null($body)) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_BODY_MISSING, '参数异常: 微信 `_body` 参数缺失：你可能用错插件顺序，应该先使用 `AddPayloadBodyPlugin`');
        }

        return $body;
    }

    /**
     * @throws InvalidConfigException 缺少商户私钥配置
     */
    public static function getWechatSign(WechatConfig $config, string $contents): string
    {
        $privateKey = $config->getMchSecretCert();

        if (empty($privateKey)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_cert]');
        }

        $privateKey = CertManager::getPrivateCert($privateKey);

        openssl_sign($contents, $sign, $privateKey, 'sha256WithRSAEncryption');

        return base64_encode($sign);
    }

    /**
     * @throws InvalidConfigException
     */
    public static function getWechatSignV2(WechatConfig $config, array $payload, bool $upper = true): string
    {
        $key = $config->getMchSecretKeyV2();

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
    public static function verifyWechatSign(ResponseInterface|ServerRequestInterface $message, array $params): void
    {
        $wechatSerial = $message->getHeaderLine('Wechatpay-Serial');
        $timestamp = $message->getHeaderLine('Wechatpay-Timestamp');
        $random = $message->getHeaderLine('Wechatpay-Nonce');
        $sign = $message->getHeaderLine('Wechatpay-Signature');
        $body = (string) $message->getBody();

        /** @var WechatConfig $wechatConfig */
        $wechatConfig = self::getProviderConfig('wechat', $params);

        $content = $timestamp."\n".$random."\n".$body."\n";
        $public = CertManager::wechatGetCertBySerial($wechatConfig->getTenant(), $wechatSerial);

        if (empty($sign)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 微信签名为空', ['headers' => $message->getHeaders(), 'body' => $body]);
        }

        $public = CertManager::getPublicCert(
            empty($public) ? self::reloadWechatPublicCerts($params, $wechatSerial) : $public
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
     * @throws InvalidConfigException 缺少商户密钥配置
     * @throws InvalidSignException   签名为空或验签失败
     */
    public static function verifyWechatSignV2(WechatConfig $config, array $destination): void
    {
        $sign = $destination['sign'] ?? null;

        if (empty($sign)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 微信签名为空', $destination);
        }

        $key = $config->getMchSecretKeyV2();

        if (empty($key)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_key_v2]');
        }

        if (self::getWechatSignV2($config, $destination) !== $sign) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证微信签名失败', $destination);
        }
    }

    public static function encryptWechatContents(string $contents, string $publicKey): ?string
    {
        if (openssl_public_encrypt($contents, $encrypted, CertManager::getPublicCert($publicKey), OPENSSL_PKCS1_OAEP_PADDING)) {
            return base64_encode($encrypted);
        }

        return null;
    }

    public static function decryptWechatContents(string $encrypted, WechatConfig $config): ?string
    {
        $privateKey = $config->getMchSecretCert() ?? '';

        if (openssl_private_decrypt(base64_decode($encrypted), $decrypted, CertManager::getPrivateCert($privateKey), OPENSSL_PKCS1_OAEP_PADDING)) {
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
    public static function reloadWechatPublicCerts(array $params, ?string $serialNo = null): string
    {
        $data = Pay::wechat()->pay(
            [StartPlugin::class, WechatPublicCertsPlugin::class, AddPayloadBodyPlugin::class, AddPayloadSignaturePlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class],
            $params
        )->get('data', []);

        /** @var WechatConfig $wechatConfig */
        $wechatConfig = self::getProviderConfig('wechat', $params);

        foreach ($data as $item) {
            $certs[$item['serial_no']] = self::decryptWechatResource($item['encrypt_certificate'], $wechatConfig)['ciphertext'] ?? '';
        }

        foreach ($certs ?? [] as $serialNo => $cert) {
            CertManager::wechatSetCertBySerial($wechatConfig->getTenant(), $serialNo, $cert);
        }

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
    public static function getWechatPublicCerts(array $params = [], ?string $path = null): void
    {
        self::reloadWechatPublicCerts($params);

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);

        $certs = CertManager::wechatGetAllCertsBySerial($config->getTenant());

        if (empty($path)) {
            var_dump($certs);

            return;
        }

        foreach ($certs as $serialNo => $cert) {
            file_put_contents($path.'/'.$serialNo.'.crt', $cert);
        }
    }

    /**
     * @throws InvalidConfigException
     * @throws DecryptException
     */
    public static function decryptWechatResource(array $resource, WechatConfig $config): array
    {
        $ciphertext = base64_decode($resource['ciphertext'] ?? '');
        $secret = $config->getMchSecretKey();

        if (strlen($ciphertext) <= Wechat::AUTH_TAG_LENGTH_BYTE) {
            throw new DecryptException(Exception::DECRYPT_WECHAT_CIPHERTEXT_PARAMS_INVALID, '加解密异常: ciphertext 位数过短');
        }

        if (empty($secret) || Wechat::MCH_SECRET_KEY_LENGTH_BYTE != strlen($secret)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_key]');
        }

        $resource['ciphertext'] = match ($resource['algorithm'] ?? '') {
            'AEAD_AES_256_GCM' => self::decryptWechatResourceAes256Gcm($ciphertext, $secret, $resource['nonce'] ?? '', $resource['associated_data'] ?? ''),
            default => throw new DecryptException(Exception::DECRYPT_WECHAT_DECRYPTED_METHOD_INVALID, '加解密异常: algorithm 不支持'),
        };

        return $resource;
    }

    /**
     * @throws DecryptException
     */
    public static function decryptWechatResourceAes256Gcm(string $ciphertext, string $secret, string $nonce, string $associatedData): array|string
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
    public static function getWechatSerialNo(array $params): string
    {
        if (!empty($params['_serial_no'])) {
            return $params['_serial_no'];
        }

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);

        $certs = CertManager::wechatGetAllCertsBySerial($config->getTenant());

        if (empty($certs)) {
            self::reloadWechatPublicCerts($params);

            /** @var WechatConfig $config */
            $config = self::getProviderConfig('wechat', $params);

            $certs = CertManager::wechatGetAllCertsBySerial($config->getTenant());
        }

        mt_srand();

        return strval(array_rand($certs));
    }

    /**
     * @throws InvalidParamsException
     */
    public static function getWechatPublicKey(WechatConfig $config, string $serialNo): string
    {
        $publicKey = CertManager::wechatGetCertBySerial($config->getTenant(), $serialNo);

        if (empty($publicKey)) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_SERIAL_NOT_FOUND, '参数异常: 微信公钥序列号未找到 - '.$serialNo);
        }

        return $publicKey;
    }
}
