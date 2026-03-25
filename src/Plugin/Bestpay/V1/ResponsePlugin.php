<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Bestpay\V1;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;

class ResponsePlugin implements PluginInterface
{
    /**
     * @throws InvalidResponseException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Bestpay][V1][ResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->validateResponse($rocket);

        Logger::info('[Bestpay][V1][ResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws InvalidResponseException
     */
    protected function validateResponse(Rocket $rocket): void
    {
        $destination = $rocket->getDestination();
        $destinationOrigin = $rocket->getDestinationOrigin();

        if ($destinationOrigin instanceof ResponseInterface
            && ($destinationOrigin->getStatusCode() < 200 || $destinationOrigin->getStatusCode() >= 300)) {
            throw new InvalidResponseException(Exception::RESPONSE_CODE_WRONG, '翼支付返回状态码异常，请检查参数是否错误', $destination);
        }

        if ($destination instanceof Collection && '0000' !== $destination->get('returnCode')) {
            throw new InvalidResponseException(Exception::RESPONSE_BUSINESS_CODE_WRONG, sprintf('翼支付返回业务异常: returnCode: %s, returnMsg: %s', $destination->get('returnCode'), $destination->get('returnMsg')), $destination);
        }
    }
}
