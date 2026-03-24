<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;

use function Yansongda\Artful\should_do_http_request;
use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\verify_alipay_v3_sign;

class ResponsePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidResponseException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Alipay][V3][ResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (should_do_http_request($rocket->getDirection())) {
            $this->validateResponse($rocket);
        }

        Logger::info('[Alipay][V3][ResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws ContainerException
     * @throws InvalidResponseException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    protected function validateResponse(Rocket $rocket): void
    {
        $response = $rocket->getDestinationOrigin();

        if (!$response instanceof ResponseInterface) {
            return;
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new InvalidResponseException(
                Exception::RESPONSE_CODE_WRONG,
                '支付宝 V3 返回状态码异常，请检查参数是否错误',
                $rocket->getDestination()
            );
        }

        $config = get_provider_config('alipay', $rocket->getParams());
        $sign = $response->getHeaderLine('alipay-signature');
        $timestamp = $response->getHeaderLine('alipay-timestamp');
        $nonce = $response->getHeaderLine('alipay-nonce');
        $body = (string) $response->getBody();

        verify_alipay_v3_sign($config, $timestamp, $nonce, $body, $sign);
    }
}
