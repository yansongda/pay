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
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::info('[wechat][LaunchPlugin] 插件开始装载', ['rocket' => $rocket]);

        if (should_do_http_request($rocket)) {
            verify_wechat_sign($rocket->getDestinationOrigin(), $rocket->getParams());

            $rocket->setDestination($this->formatResponse($rocket));
        }

        Logger::info('[wechat][LaunchPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    protected function formatResponse(Rocket $rocket): Collection
    {
        $response = $rocket->getDestination();

        $code = $response->get('code');

        if (!is_null($code) && 0 != $code) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_CODE);
        }

        return $response;
    }
}
