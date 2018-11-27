<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Gateways\Alipay;
use Yansongda\Pay\Log;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;
use Yansongda\Supports\Traits\HasHttpRequest;

/**
 * @author yansongda <me@yansongda.cn>
 *
 * @property string app_id alipay app_id
 * @property string ali_public_key
 * @property string private_key
 * @property array http http options
 * @property string mode current mode
 * @property array log log options
 */
class Support
{
    use HasHttpRequest;

    /**
     * Alipay gateway.
     *
     * @var string
     */
    protected $baseUri;

    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Instance.
     *
     * @var Support
     */
    private static $instance;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param Config $config
     */
    private function __construct(Config $config)
    {
        $this->baseUri = Alipay::URL[$config->get('mode', Alipay::MODE_NORMAL)];
        $this->config = $config;
        $this->setHttpOptions();
    }

    /**
     * __get.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param $key
     *
     * @return mixed|null|Config
     */
    public function __get($key)
    {
        return $this->getConfig($key);
    }

    /**
     * Get Base Uri.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * Get instance.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param Config|null $config
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    public static function getInstance($config = null): self
    {
        if ((!(self::$instance instanceof self)) && is_null($config)) {
            throw new InvalidArgumentException('Must Initialize Support With Config Before Using');
        }

        if (!(self::$instance instanceof self)) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Get Alipay API result.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $data
     *
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws InvalidArgumentException
     *
     * @return Collection
     */
    public static function requestApi(array $data): Collection
    {
        Log::debug('Request To Alipay Api', [self::getInstance()->getBaseUri(), $data]);

        $data = array_filter($data, function ($value) {
            return ($value == '' || is_null($value)) ? false : true;
        });

        $result = mb_convert_encoding(self::getInstance()->post('', $data), 'utf-8', 'gb2312');
        $result = json_decode($result, true);

        Log::debug('Result Of Alipay Api', $result);

        $method = str_replace('.', '_', $data['method']).'_response';

        if (!isset($result['sign']) || $result[$method]['code'] != '10000') {
            throw new GatewayException(
                'Get Alipay API Error:'.$result[$method]['msg'].($result[$method]['sub_code'] ?? ''),
                $result,
                $result[$method]['code']
            );
        }

        if (self::verifySign($result[$method], true, $result['sign'])) {
            return new Collection($result[$method]);
        }

        Log::warning('Alipay Sign Verify FAILED', $result);

        throw new InvalidSignException('Alipay Sign Verify FAILED', $result);
    }

    /**
     * Generate sign.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $params
     *
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public static function generateSign(array $params): string
    {
        $privateKey = self::getInstance()->private_key;

        if (is_null($privateKey)) {
            throw new InvalidConfigException('Missing Alipay Config -- [private_key]');
        }

        if (Str::endsWith($privateKey, '.pem')) {
            $privateKey = openssl_pkey_get_private($privateKey);
        } else {
            $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".
                wordwrap($privateKey, 64, "\n", true).
                "\n-----END RSA PRIVATE KEY-----";
        }

        openssl_sign(self::getSignContent($params), $sign, $privateKey, OPENSSL_ALGO_SHA256);

        $sign = base64_encode($sign);

        Log::debug('Alipay Generate Sign Before Base64', [$params, $sign]);

        return $sign;
    }

    /**
     * Verify sign.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param array       $data
     * @param bool        $sync
     * @param string|null $sign
     *
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    public static function verifySign(array $data, $sync = false, $sign = null): bool
    {
        $publicKey = self::getInstance()->ali_public_key;

        if (is_null($publicKey)) {
            throw new InvalidConfigException('Missing Alipay Config -- [ali_public_key]');
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

        Log::debug('Alipay Generate Sign Content Before Trim', [$data, $stringToBeSigned]);

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
     * Initialize.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param Config $config
     *
     * @throws InvalidArgumentException
     *
     * @return Support
     */
    public static function initialize(Config $config): self
    {
        return self::getInstance($config);
    }

    /**
     * Get service config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param null|string $key
     * @param null|mixed  $default
     *
     * @return mixed|null
     */
    public function getConfig($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->config->all();
        }

        if ($this->config->has($key)) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Set Http options.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return self
     */
    protected function setHttpOptions(): self
    {
        if ($this->config->has('http') && is_array($this->config->get('http'))) {
            $this->config->forget('http.base_uri');
            $this->httpOptions = $this->config->get('http');
        }

        return $this;
    }
}
