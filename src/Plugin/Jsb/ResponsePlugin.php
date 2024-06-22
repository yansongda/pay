<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Jsb;

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

        Logger::debug('[Jsb][ResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->validateResponse($rocket);

        Logger::info('[Jsb][ResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

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
            throw new InvalidResponseException(Exception::RESPONSE_CODE_WRONG, '江苏银行返回状态码异常，请检查参数是否错误', $rocket->getDestination());
        }

        if ($destination instanceof Collection && '000000' !== $destination->get('respCode')) {
            throw new InvalidResponseException(Exception::RESPONSE_BUSINESS_CODE_WRONG, sprintf('江苏银行返回错误: respCode:%s respMsg:%s', $destination->get('respCode'), $destination->get('respMsg')), $rocket->getDestination());
        }
    }
}
