<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_alipay_config;
use function Yansongda\Pay\get_private_cert;

class AddSignaturePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][AddSignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload(['sign' => $this->getSign($rocket)]);

        Logger::info('[Alipay][AddSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function getSign(Rocket $rocket): string
    {
        $privateKey = $this->getPrivateKey($rocket->getParams());

        $content = $rocket->getPayload()->sortKeys()->toString();

        openssl_sign($content, $sign, $privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($sign);
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function getPrivateKey(array $params): string
    {
        $privateKey = get_alipay_config($params)['app_secret_cert'] ?? null;

        if (is_null($privateKey)) {
            throw new InvalidConfigException(Exception::ALIPAY_CONFIG_INVALID, 'Missing Alipay Config -- [app_secret_cert]');
        }

        return get_private_cert($privateKey);
    }
}
