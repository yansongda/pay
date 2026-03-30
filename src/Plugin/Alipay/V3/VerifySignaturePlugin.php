<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\InvalidSignException;

use function Yansongda\Artful\should_do_http_request;
use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\verify_alipay_sign;

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

        Logger::debug('[Alipay][V3][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (!should_do_http_request($rocket->getDirection()) || !($rocket->getDestinationOrigin() instanceof ResponseInterface)) {
            return $rocket;
        }

        $response = $rocket->getDestinationOrigin();
        $config = get_provider_config('alipay', $rocket->getParams());

        $body = (string) $response->getBody();
        $content = $response->getHeaderLine('alipay-timestamp')."\n"
            .$response->getHeaderLine('alipay-nonce')."\n"
            .(empty($body) ? '' : $body)."\n";

        verify_alipay_sign(
            $config,
            $content,
            $response->getHeaderLine('alipay-signature'),
        );

        Logger::info('[Alipay][V3][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
