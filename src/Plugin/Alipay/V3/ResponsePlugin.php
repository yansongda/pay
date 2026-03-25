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

        if (!($response instanceof ResponseInterface)
            || ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            return;
        }

        $body = json_decode((string) $response->getBody(), true);
        $message = is_array($body)
            ? ($body['sub_msg'] ?? $body['message'] ?? $body['msg'] ?? '未知错误，请查看支付宝原始响应')
            : '未知错误，请查看支付宝原始响应';

        throw new InvalidResponseException(Exception::RESPONSE_CODE_WRONG, '支付宝 V3 响应异常: '.$message, $rocket->getDestination());
    }
}
