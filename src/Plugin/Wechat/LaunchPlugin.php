<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class LaunchPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[wechat][LaunchPlugin] 插件开始装载', ['rocket' => $rocket]);

        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        if (should_do_http_request($rocket)) {
            $this->verifySign($rocket);

            $rocket->setDestination($this->formatResponse($rocket));
        }

        Logger::info('[wechat][LaunchPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    protected function verifySign(Rocket $rocket): void
    {
        // todo
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    protected function formatResponse(Rocket $rocket): Collection
    {
        $response = $rocket->getDestination();

        if (isset($response['code'])) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_CODE);
        }

        return $response;
    }
}
