<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_provider_config;

class AddPayloadSignPlugin implements PluginInterface
{
    /**
     * @throws ServiceNotFoundException
     * @throws InvalidParamsException
     * @throws ContainerException|InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Epay][AddPayloadSignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('epay', $params);
        $payload = $rocket->getPayload();

        if (empty($payload) || $payload->isEmpty()) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: epay支付必要参数缺失。可能插件用错顺序，应该先使用 `业务插件`');
        }

        $pkey = $this->getPrivateKey($config);
        $sign = $this->getSignature($pkey, $payload);
        $rocket->mergePayload([
            'signType' => 'RSA',
            'sign' => $sign,
        ]);

        Logger::info('[Epay][AddPayloadSignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getSignature(string $pkey, Collection $payload): string
    {
        $content = $payload->sortKeys()->toString();

        openssl_sign($content, $signature, $pkey);

        return base64_encode($signature);
    }

    protected function getPrivateKey(array $config): string
    {
        $privateCertPath = $config['mch_secret_cert_path'] ?? '';

        if (!$privateCertPath) {
            throw new InvalidConfigException(Exception::CONFIG_EPAY_INVALID, '参数异常: epay支付配置文件中未找到 `mch_secret_cert_path` 配置项。可能插件用错顺序，应该先使用 `StartPlugin`');
        }

        return file_get_contents($privateCertPath);
    }
}
