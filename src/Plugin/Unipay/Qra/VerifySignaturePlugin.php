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
use Yansongda\Pay\Exception\InvalidSignException;

use function Yansongda\Artful\should_do_http_request;
use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\verify_unipay_sign_qra;

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

        Logger::debug('[Unipay][Qra][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_provider_config('unipay', $rocket->getParams());

        if (!should_do_http_request($rocket->getDirection())) {
            return $rocket;
        }

        verify_unipay_sign_qra($config, $rocket->getDestination()?->all() ?? []);

        Logger::info('[Unipay][Qra][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
