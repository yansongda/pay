<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Trade;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

class GetClientTokenResponsePlugin implements PluginInterface
{
    /**
     * @throws InvalidResponseException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Douyin][V1][Trade][GetClientTokenResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->validateResponse($rocket);

        Logger::info('[Douyin][V1][Trade][GetClientTokenResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws InvalidResponseException
     */
    protected function validateResponse(Rocket $rocket): void
    {
        $destination = $rocket->getDestination();
        $response = $rocket->getDestinationOrigin();

        if ($response instanceof ResponseInterface
            && ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)) {
            throw new InvalidResponseException(Exception::RESPONSE_CODE_WRONG, '抖音返回状态码异常，请检查参数是否错误', $destination);
        }

        if (0 !== (int) $destination->get('data.error_code')) {
            throw new InvalidResponseException(Exception::RESPONSE_BUSINESS_CODE_WRONG, '抖音获取 client_token 失败: '.$destination->get('data.description'), $destination);
        }
    }
}
