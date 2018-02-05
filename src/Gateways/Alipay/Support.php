<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Log;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;
use Yansongda\Supports\Traits\HasHttpRequest;

class Support
{
    use HasHttpRequest;

    /**
     * Instance.
     *
     * @var Support
     */
    private static $instance;

    /**
     * Alipay gateway.
     *
     * @var string
     */
    protected $baseUri = 'https://openapi.alipay.com/gateway.do';

    /**
     * Get instance.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return Support
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get Alipay API result.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array  $data
     * @param string $publicKey
     *
     * @return Collection
     */
    public static function requestApi(array $data, $publicKey): Collection
    {
        Log::debug('Request To Alipay Api', [self::getInstance()->baseUri(), $data]);

        $method = str_replace('.', '_', $data['method']).'_response';

        $result = mb_convert_encoding(self::getInstance()->post('', $data), 'utf-8', 'gb2312');
        $result = json_decode($result, true);

        if (!self::verifySign($result[$method], $publicKey, true, $result['sign'])) {
            Log::warning('Alipay Sign Verify FAILED', $result);

            throw new InvalidSignException('Alipay Sign Verify FAILED', 3, $result);
        }

        if (isset($result[$method]['code']) && $result[$method]['code'] == '10000') {
            return new Collection($result[$method]);
        }

        throw new GatewayException(
            'Get Alipay API Error:'.$result[$method]['msg'],
            $result[$method]['code'],
            $result
        );
    }

    /**
     * Generate sign.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array  $parmas
     * @param string $privateKey
     *
     * @return string
     */
    public static function generateSign(array $parmas, $privateKey = null): string
    {
        if (is_null($privateKey)) {
            throw new InvalidConfigException('Missing Alipay Config -- [private_key]', 1);
        }

        if (Str::endsWith($privateKey, '.pem')) {
            $privateKey = openssl_pkey_get_private($privateKey);
        } else {
            $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".
                wordwrap($privateKey, 64, "\n", true).
                "\n-----END RSA PRIVATE KEY-----";
        }

        openssl_sign(self::getSignContent($parmas), $sign, $privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($sign);
    }

    /**
     * Verfiy sign.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param array       $data
     * @param string      $publicKey
     * @param bool        $sync
     * @param string|null $sign
     *
     * @return bool
     */
    public static function verifySign(array $data, $publicKey = null, $sync = false, $sign = null): bool
    {
        if (is_null($publicKey)) {
            throw new InvalidConfigException('Missing Alipay Config -- [ali_public_key]', 2);
        }

        if (Str::endsWith($publicKey, '.pem')) {
            $publicKey = openssl_pkey_get_public($publicKey);
        } else {
            $publicKey = "-----BEGIN PUBLIC KEY-----\n".
                wordwrap($publicKey, 64, "\n", true).
                "\n-----END PUBLIC KEY-----";
        }

        $sign = $sign ?? $data['sign'];

        $toVerify = $sync ? mb_convert_encoding(json_encode($data, JSON_UNESCAPED_UNICODE), 'gb2312', 'utf-8') :
                            self::getSignContent($data, true);

        return openssl_verify($toVerify, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    /**
     * Get signContent that is to be signed.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $data
     * @param bool  $verify
     *
     * @return string
     */
    public static function getSignContent(array $data, $verify = false): string
    {
        $data = self::encoding($data, $data['charset'] ?? 'gb2312', 'utf-8');

        ksort($data);

        $stringToBeSigned = '';
        foreach ($data as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
        }

        return trim($stringToBeSigned, '&');
    }

    /**
     * Convert encoding.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string|array $data
     * @param string       $to
     * @param string       $from
     *
     * @return array
     */
    public static function encoding($data, $to = 'utf-8', $from = 'gb2312'): array
    {
        return Arr::encoding((array) $data, $to, $from);
    }

    /**
     * Alipay gateway.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $mode
     *
     * @return string
     */
    public static function baseUri($mode = null): string
    {
        switch ($mode) {
            case 'dev':
                self::getInstance()->baseUri = 'https://openapi.alipaydev.com/gateway.do';
                break;

            default:
                break;
        }

        return self::getInstance()->baseUri;
    }
}
