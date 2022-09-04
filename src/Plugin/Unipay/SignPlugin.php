<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;

use function Yansongda\Pay\get_unipay_config;

use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\GetUnipayCerts;
use Yansongda\Supports\Collection;

class SignPlugin implements PluginInterface
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

        $payload = $rocket->getPayload()->filter(fn ($v, $k) => 'signature' != $k);
        $params = $rocket->getParams();
        $config = get_unipay_config($params);

        if (empty($config['certs']['pkey'])) {
            $this->getCertId($params['_config'] ?? 'default', $config);

            $config = get_unipay_config($params);
        }

        $rocket->mergePayload([
            'signature' => $this->getSign($config['certs']['pkey'] ?? '', $payload),
        ]);

        Logger::info('[unipay][PreparePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getSign(string $pkey, Collection $payload): string
    {
        $content = $payload->sortKeys()->toString();

        openssl_sign(hash('sha256', $content), $sign, $pkey, 'sha256');

        return base64_encode($sign);
    }
}
