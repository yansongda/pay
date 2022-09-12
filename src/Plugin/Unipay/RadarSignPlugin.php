<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay;

use Closure;
use GuzzleHttp\Psr7\Utils;
use Yansongda\Pay\Contract\PluginInterface;

use function Yansongda\Pay\get_unipay_config;

use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\GetUnipayCerts;
use Yansongda\Supports\Collection;

class RadarSignPlugin implements PluginInterface
{
    use GetUnipayCerts;

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[unipay][PreparePlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->sign($rocket);

        $this->reRadar($rocket);

        Logger::info('[unipay][PreparePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function sign(Rocket $rocket): void
    {
        $payload = $rocket->getPayload()->filter(fn ($v, $k) => 'signature' != $k);
        $config = $this->getConfig($rocket->getParams());

        $rocket->mergePayload([
            'signature' => $this->getSignature($config['certs']['pkey'] ?? '', $payload),
        ]);
    }

    protected function reRadar(Rocket $rocket): void
    {
        $body = $this->getBody($rocket->getPayload());
        $radar = $rocket->getRadar();

        if (!empty($body) && !empty($radar)) {
            $radar = $radar->withBody(Utils::streamFor($body));

            $rocket->setRadar($radar);
        }
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getConfig(array $params): array
    {
        $config = get_unipay_config($params);

        if (empty($config['certs']['pkey'])) {
            $this->getCertId($params['_config'] ?? 'default', $config);

            $config = get_unipay_config($params);
        }

        return $config;
    }

    protected function getSignature(string $pkey, Collection $payload): string
    {
        $content = $payload->sortKeys()->toString();

        openssl_sign(hash('sha256', $content), $sign, $pkey, 'sha256');

        return base64_encode($sign);
    }

    protected function getBody(Collection $payload): string
    {
        return $payload->query();
    }
}
