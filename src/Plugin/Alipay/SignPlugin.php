<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class SignPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $this->filterPayload($rocket);

        $privateKey = $this->getPrivateKey($rocket->getParams());

        openssl_sign($this->getSignContent($rocket->getPayload()), $sign, $privateKey, OPENSSL_ALGO_SHA256);

        $sign = base64_encode($sign);

        !is_resource($privateKey) ?: openssl_free_key($privateKey);

        $rocket->mergePayload(['sign' => $sign]);

        return $next($rocket);
    }

    protected function filterPayload(Rocket $rocket): void
    {
        $payload = $rocket->getPayload()->filter(function ($v, $k) {
            return '' !== $v && !is_null($v) && 'sign' != $k;
        });

        $contents = array_filter($payload->get('biz_content', []), function ($v, $k) {
            return !Str::startsWith($k, '_');
        }, ARRAY_FILTER_USE_BOTH);

        $rocket->setPayload(
            $payload->merge(['biz_content' => json_encode($contents)])
        );
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
        $privateKey = get_alipay_config($params)->get('app_secret_cert');

        if (is_null($privateKey)) {
            throw new InvalidConfigException(InvalidConfigException::ALIPAY_CONFIG_ERROR, 'Missing Alipay Config -- [app_secret_cert]');
        }

        return get_public_crt_or_private_cert($privateKey);
    }

    protected function getSignContent(Collection $payload): string
    {
        return $payload->sortKeys()->toString();
    }
}
