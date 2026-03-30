<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

class ResponsePlugin implements PluginInterface
{
    /**
     * @throws InvalidResponseException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Alipay][V3][ResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->validateResponse($rocket);

        Logger::info('[Alipay][V3][ResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws InvalidResponseException
     */
    protected function validateResponse(Rocket $rocket): void
    {
        $response = $rocket->getDestinationOrigin();

        if ((!($response instanceof ResponseInterface))
            || ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            return;
        }

        $destination = $rocket->getDestination();
        $message = sprintf(
            '支付宝返回状态码异常，请检查参数是否错误。code: %s, message: %s',
            $destination->get('code', '未知'),
            $destination->get('message', '未知'),
        );

        throw new InvalidResponseException(Exception::RESPONSE_CODE_WRONG, $message, $rocket->getDestination());
    }
}
