<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_alipay_config;
use function Yansongda\Pay\should_do_http_request;
use function Yansongda\Pay\verify_unipay_sign;

class VerifySignaturePlugin implements PluginInterface
{
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
        $config = get_alipay_config($params);

        verify_unipay_sign(
            $config,
            $destination->except('signature')->sortKeys()->toString(),
            $destination->get('signature', ''),
            $destination->get('signPubKeyCert')
        );

        Logger::info('[Unipay][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
