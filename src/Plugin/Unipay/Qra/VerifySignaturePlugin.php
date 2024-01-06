<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Qra;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_unipay_config;
use function Yansongda\Pay\should_do_http_request;
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

        $config = get_unipay_config($rocket->getParams());

        if (!should_do_http_request($rocket->getDirection())) {
            return $rocket;
        }

        verify_unipay_sign_qra($config, $rocket->getDestination()?->all() ?? []);

        Logger::info('[Unipay][Qra][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
