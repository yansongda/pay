<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\should_do_http_request;

class LaunchPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::info('[unipay][LaunchPlugin] 插件开始装载', ['rocket' => $rocket]);

        if (should_do_http_request($rocket->getDirection())) {
            // todo: 验证签名

            $rocket->setDestination($this->validateResponse($rocket));
        }

        Logger::info('[unipay][LaunchPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @return array|\Psr\Http\Message\MessageInterface|\Yansongda\Supports\Collection|null
     *
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    protected function validateResponse(Rocket $rocket)
    {
        $response = $rocket->getDestination();

        if ($response instanceof ResponseInterface &&
            ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)) {
            throw new InvalidResponseException(Exception::INVALID_RESPONSE_CODE);
        }

        return $response;
    }
}
