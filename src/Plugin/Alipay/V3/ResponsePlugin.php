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
     * 非 2xx 响应：将错误体 `code`/`message`（均为 string，见 OAS `CommonErrorType`）并入异常消息后抛出.
     *
     * @throws InvalidResponseException
     */
    protected function validateResponse(Rocket $rocket): void
    {
        $response = $rocket->getDestinationOrigin();

        if (!$response instanceof ResponseInterface
            || ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            return;
        }

        $error = json_decode((string) $response->getBody(), true);

        $message = '支付宝返回状态码异常，请检查参数是否错误';

        if (is_array($error) && (isset($error['code']) || isset($error['message']))) {
            $message = sprintf('支付宝返回状态码异常: [%s] %s，请检查参数是否错误', $error['code'] ?? '', $error['message'] ?? '');
        }

        throw new InvalidResponseException(Exception::RESPONSE_CODE_WRONG, $message, $rocket->getDestination());
    }
}
