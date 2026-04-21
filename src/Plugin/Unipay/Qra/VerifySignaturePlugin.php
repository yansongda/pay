<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Qra;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\UnipayTrait;

use function Yansongda\Artful\should_do_http_request;

class VerifySignaturePlugin implements PluginInterface
{
    use UnipayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Unipay][Qra][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        /** @var UnipayConfig $config */
        $config = self::getProviderConfig('unipay', $rocket->getParams());

        if (!should_do_http_request($rocket->getDirection())) {
            return $rocket;
        }

        self::verifyUnipaySignQra($config, $rocket->getDestination()?->all() ?? []);

        Logger::info('[Unipay][Qra][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
