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
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\UnipayTrait;
use Yansongda\Supports\Collection;

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

        Logger::debug('[Unipay][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (!should_do_http_request($rocket->getDirection())) {
            return $rocket;
        }

        $destination = $rocket->getDestination();

        if (!$destination instanceof Collection) {
            return $rocket;
        }

        $params = $rocket->getParams();

        /** @var UnipayConfig $config */
        $config = self::getProviderConfig('unipay', $params);

        self::verifyUnipaySign(
            $config,
            $destination->except('signature')->sortKeys()->toString(),
            $destination->get('signature', ''),
            $destination->get('signPubKeyCert')
        );

        Logger::info('[Unipay][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
