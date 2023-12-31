<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
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

        Logger::debug('[Wechat][ResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setDestination($this->validateResponse($rocket));

        Logger::info('[Wechat][ResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws InvalidResponseException
     */
    protected function validateResponse(Rocket $rocket): null|array|Collection|MessageInterface
    {
        $response = $rocket->getDestination();

        if ($response instanceof ResponseInterface
            && ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)) {
            throw new InvalidResponseException(Exception::RESPONSE_CODE_WRONG);
        }

        return $response;
    }
}
