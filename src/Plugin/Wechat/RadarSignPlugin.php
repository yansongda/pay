<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;

use function Yansongda\Pay\get_public_cert;
use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_sign;

use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\to_xml;

use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class RadarSignPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Exception
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[wechat][RadarSignPlugin] 插件开始装载', ['rocket' => $rocket]);

        switch ($rocket->getParams()['_version'] ?? 'default') {
            case 'v2':
                $radar = $this->v2($rocket);
                break;
            default:
                $radar = $this->v3($rocket);
                break;
        }

        $rocket->setRadar($radar);

        Logger::info('[wechat][RadarSignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    protected function v2(Rocket $rocket): RequestInterface
    {
        $config = get_wechat_config($rocket->getParams());
        $payload = $rocket->getPayload();

        $body = $payload->all();
        $body['sign'] = $this->v2GetSign($config['mch_secret_key_v2'] ?? '', $payload);

        return $rocket->getRadar()->withBody(Utils::streamFor(to_xml($body)));
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    protected function v2GetSign(?string $secret, Collection $payload): string
    {
        if (empty($secret)) {
            throw new InvalidConfigException(Exception::WECHAT_CONFIG_ERROR, 'Missing Wechat Config -- [mch_secret_key_v2]');
        }

        $string = md5($this->v2PayloadToString($payload).'&key='.$secret);

        return strtoupper($string);
    }

    protected function v2PayloadToString(Collection $payload): string
    {
        $data = $payload->all();

        ksort($data);

        $buff = '';

        foreach ($data as $k => $v) {
            $buff .= ('sign' != $k && '' != $v && !is_array($v)) ? $k.'='.$v.'&' : '';
        }

        return trim($buff, '&');
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Exception
     */
    protected function v3(Rocket $rocket): RequestInterface
    {
        $timestamp = time();
        $random = Str::random(32);
        $body = $this->v3PayloadToString($rocket->getPayload());
        $contents = $this->v3GetContents($rocket, $timestamp, $random);
        $authorization = $this->v3GetWechatAuthorization($rocket->getParams(), $timestamp, $random, $contents);
        $radar = $rocket->getRadar()->withHeader('Authorization', $authorization);

        if (!empty($rocket->getParams()['_serial_no'])) {
            $radar = $radar->withHeader('Wechatpay-Serial', $rocket->getParams()['_serial_no']);
        }

        if (!empty($body)) {
            $radar = $radar->withBody(Utils::streamFor($body));
        }

        return $radar;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function v3GetWechatAuthorization(array $params, int $timestamp, string $random, string $contents): string
    {
        $config = get_wechat_config($params);
        $mchPublicCertPath = $config['mch_public_cert_path'] ?? null;

        if (empty($mchPublicCertPath)) {
            throw new InvalidConfigException(Exception::WECHAT_CONFIG_ERROR, 'Missing Wechat Config -- [mch_public_cert_path]');
        }

        $ssl = openssl_x509_parse(get_public_cert($mchPublicCertPath));

        if (empty($ssl['serialNumberHex'])) {
            throw new InvalidConfigException(Exception::WECHAT_CONFIG_ERROR, 'Parse [mch_public_cert_path] Serial Number Error');
        }

        $auth = sprintf(
            'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $config['mch_id'] ?? '',
            $random,
            $timestamp,
            $ssl['serialNumberHex'],
            get_wechat_sign($params, $contents),
        );

        return 'WECHATPAY2-SHA256-RSA2048 '.$auth;
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function v3GetContents(Rocket $rocket, int $timestamp, string $random): string
    {
        $request = $rocket->getRadar();

        if (is_null($request)) {
            throw new InvalidParamsException(Exception::REQUEST_NULL_ERROR);
        }

        $uri = $request->getUri();

        return $request->getMethod()."\n".
            $uri->getPath().(empty($uri->getQuery()) ? '' : '?'.$uri->getQuery())."\n".
            $timestamp."\n".
            $random."\n".
            $this->v3PayloadToString($rocket->getPayload())."\n";
    }

    protected function v3PayloadToString(?Collection $payload): string
    {
        return (is_null($payload) || 0 === $payload->count()) ? '' : $payload->toJson();
    }
}
