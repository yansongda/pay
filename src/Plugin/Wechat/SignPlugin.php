<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use GuzzleHttp\Psr7\Utils;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class SignPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Exception
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[wechat][SignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $timestamp = time();
        $random = Str::random(32);
        $body = $this->payloadToString($rocket->getPayload());
        $radar = $rocket->getRadar()->withAddedHeader('Authorization', get_wechat_authorization(
            $rocket->getParams(), $timestamp, $random, $this->getContents($rocket, $timestamp, $random))
        );

        if (!empty($body)) {
            $radar = $radar->withBody(Utils::streamFor($body));
        }

        $rocket->setRadar($radar);

        Logger::info('[wechat][SignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getContents(Rocket $rocket, int $timestamp, string $random): string
    {
        $request = $rocket->getRadar();

        if (is_null($request)) {
            throw new InvalidParamsException(InvalidParamsException::REQUEST_NULL_ERROR);
        }

        $uri = $request->getUri();

        return $request->getMethod()."\n".
            $uri->getPath().(empty($uri->getQuery()) ? '' : '?'.$uri->getQuery())."\n".
            $timestamp."\n".
            $random."\n".
            $this->payloadToString($rocket->getPayload())."\n";
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    protected function getMchPublicCertSerialNumber(?string $path): string
    {
        if (empty($path)) {
            throw new InvalidConfigException(InvalidConfigException::WECHAT_CONFIG_ERROR, 'Missing Wechat Config -- [mch_public_cert_path]');
        }

        $cert = file_get_contents($path);
        $ssl = openssl_x509_parse($cert);

        if (empty($ssl['serialNumberHex'])) {
            throw new InvalidConfigException(InvalidConfigException::WECHAT_CONFIG_ERROR, 'Parse [mch_public_cert_path] Serial Number Error');
        }

        return $ssl['serialNumberHex'];
    }

    protected function payloadToString(?Collection $payload): string
    {
        return (is_null($payload) || 0 === $payload->count()) ? '' : $payload->toJson();
    }
}
