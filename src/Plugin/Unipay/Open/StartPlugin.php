<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Open;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\UnipayTrait;

class StartPlugin implements PluginInterface
{
    use UnipayTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var UnipayConfig $config */
        $config = self::getProviderConfig('unipay', $params);

        $rocket->mergePayload(array_merge($params, [
            '_unpack_raw' => true,
            'certId' => $this->getCertId($config),
        ]));

        Logger::info('[Unipay][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function getCertId(UnipayConfig $config): string
    {
        $path = $config->getMchCertPath();
        $password = $config->getMchCertPassword();

        if (empty($path) || empty($password)) {
            throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常: 缺少银联配置 -- [mch_cert_path] or [mch_cert_password]');
        }

        return CertManager::unipayGetCertId($path, $password);
    }
}
