<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Str;

class SignPlugin implements PluginInterface
{
    /**
     * @throws \Exception
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[wechat][SignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->filterPayload($rocket);

        $rocket->setRadar($rocket->getRadar()
            ->withAddedHeader('Authorization', $this->getAuthorization($rocket))
        );

        Logger::info('[wechat][SignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function filterPayload(Rocket $rocket): void
    {
        $payload = $rocket->getPayload()->filter(function ($v, $k) {
            return !Str::startsWith(strval($k), '_');
        });

        $rocket->setPayload($payload);
    }

    /**
     * @throws \Exception
     */
    protected function getAuthorization(Rocket $rocket): string
    {
        $timestamp = time();
        $random = Str::random(32);
        $config = get_wechat_config($rocket->getParams());

        $auth = sprintf(
            'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $config->get('mch_id', ''),
            $random,
            $timestamp,
            $this->getMchPublicCertSerialNumber($config->get('mch_public_cert_path')),
            $this->getSign($rocket, $timestamp, $random)
        );

        return 'WECHATPAY2-SHA256-RSA2048 '.$auth;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getSign(Rocket $rocket, int $timestamp, string $random): string
    {
        $request = $rocket->getRadar();

        if (is_null($request)) {
            throw new InvalidParamsException(InvalidParamsException::REQUEST_NULL_ERROR);
        }

        $uri = $request->getUri();

        $contents = $request->getMethod().'\n'.
            $uri->getPath().(empty($uri->getQuery()) ? '' : '?'.$uri->getQuery()).'\n'.
            $timestamp.'\n'.
            $random.'\n'.
            $request->getBody()->getContents().'\n';

        $privateKey = $this->getPrivateKey($rocket->getParams());

        openssl_sign($contents, $sign, $privateKey, 'sha256WithRSAEncryption');

        $sign = base64_encode($sign);

        !is_resource($privateKey) ?: openssl_free_key($privateKey);

        return $sign;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return false|resource|string
     */
    protected function getPrivateKey(array $params)
    {
        $privateKey = get_wechat_config($params)->get('mch_secret_cert');

        if (is_null($privateKey)) {
            throw new InvalidConfigException(InvalidConfigException::WECHAT_CONFIG_ERROR, 'Missing Wechat Config -- [app_secret_cert]');
        }

        return get_public_crt_or_private_cert($privateKey);
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    protected function getMchPublicCertSerialNumber(?string $path): string
    {
        if (is_null($path)) {
            throw new InvalidConfigException(InvalidConfigException::WECHAT_CONFIG_ERROR, 'Missing Wechat Config -- [mch_public_cert_path]');
        }

        $cert = file_get_contents($path);
        $ssl = openssl_x509_parse($cert);

        if (empty($ssl['serialNumberHex'])) {
            throw new InvalidConfigException(InvalidConfigException::WECHAT_CONFIG_ERROR, 'Parse [mch_public_cert_path] Serial Number Error');
        }

        return $ssl['serialNumberHex'];
    }
}
