<?php

namespace Hanwenbo\Pay\Gateways\Wechat;

use Hanwenbo\Pay\Exceptions\GatewayException;
use Hanwenbo\Pay\Exceptions\InvalidArgumentException;
use Hanwenbo\Pay\Exceptions\InvalidSignException;
use Hanwenbo\Pay\Gateways\Wechat;
use Hanwenbo\Pay\Log;
use Hanwenbo\Supports\Collection;
use Hanwenbo\Supports\Traits\HasHttpRequest;

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
     * Wechat gateway.
     *
     * @var string
     */
    protected $baseUri = 'https://api.mch.weixin.qq.com/';

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
     * Request wechat api.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string      $endpoint
     * @param array       $data
     * @param string|null $key
     * @param string      $certClient
     * @param string      $certKey
     *
     * @return Collection
     */
    public static function requestApi($endpoint, $data, $key = null, $certClient = null, $certKey = null): Collection
    {
        Log::debug('Request To Wechat Api', [self::baseUri().$endpoint, $data]);

        $result = self::getInstance()->post(
            $endpoint,
            self::toXml($data),
            ($certClient !== null && $certKey !== null) ? ['cert' => $certClient, 'ssl_key' => $certKey] : []
        );
        $result = is_array($result) ? $result : self::fromXml($result);

        if (!isset($result['return_code']) || $result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') {
            throw new GatewayException(
                'Get Wechat API Error:'.$result['return_msg'].($result['err_code_des'] ?? ''),
                20000,
                $result
            );
        }

        if (strpos($endpoint, 'mmpaymkttransfers') !== false || self::generateSign($result, $key) === $result['sign']) {
            return new Collection($result);
        }

        Log::warning('Wechat Sign Verify FAILED', $result);

        throw new InvalidSignException('Wechat Sign Verify FAILED', 3, $result);
    }

    /**
     * Filter payload.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array                      $payload
     * @param array|string               $order
     * @param \Hanwenbo\Supports\Config $config
     *
     * @return array
     */
    public static function filterPayload($payload, $order, $config)
    {
        $payload = array_merge($payload, is_array($order) ? $order : ['out_trade_no' => $order]);

        $type = isset($order['type']) ? $order['type'].($order['type'] == 'app' ? '' : '_').'id' : 'app_id';

        $payload['appid'] = $config->get($type, '');
        $mode = $config->get('mode', Wechat::MODE_NORMAL);
        if ($mode === Wechat::MODE_SERVICE) {
            $payload['sub_appid'] = $config->get('sub_'.$type, '');
        }

        unset($payload['notify_url'], $payload['trade_type'], $payload['type']);

        $payload['sign'] = self::generateSign($payload, $config->get('key'));

        return $payload;
    }

    /**
     * Generate wechat sign.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $data
     *
     * @return string
     */
    public static function generateSign($data, $key = null): string
    {
        if (is_null($key)) {
            throw new InvalidArgumentException('Missing Wechat Config -- [key]', 1);
        }

        ksort($data);

        $string = md5(self::getSignContent($data).'&key='.$key);

        return strtoupper($string);
    }

    /**
     * Generate sign content.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $data
     *
     * @return string
     */
    public static function getSignContent($data): string
    {
        $buff = '';

        foreach ($data as $k => $v) {
            $buff .= ($k != 'sign' && $v != '' && !is_array($v)) ? $k.'='.$v.'&' : '';
        }

        return trim($buff, '&');
    }

    /**
     * Convert array to xml.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $data
     *
     * @return string
     */
    public static function toXml($data): string
    {
        if (!is_array($data) || count($data) <= 0) {
            throw new InvalidArgumentException('Convert To Xml Error! Invalid Array!', 2);
        }

        $xml = '<xml>';
        foreach ($data as $key => $val) {
            $xml .= is_numeric($val) ? '<'.$key.'>'.$val.'</'.$key.'>' :
                                       '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
        }
        $xml .= '</xml>';

        return $xml;
    }

    /**
     * Convert xml to array.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $xml
     *
     * @return array
     */
    public static function fromXml($xml): array
    {
        if (!$xml) {
            throw new InvalidArgumentException('Convert To Array Error! Invalid Xml!', 3);
        }

        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * Wechat gateway.
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
            case Wechat::MODE_DEV:
                self::getInstance()->baseUri = 'https://api.mch.weixin.qq.com/sandboxnew/';
                break;

            case Wechat::MODE_HK:
                self::getInstance()->baseUri = 'https://apihk.mch.weixin.qq.com/';
                break;

            default:
                break;
        }

        return self::getInstance()->baseUri;
    }
}
