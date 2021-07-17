<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class CallbackPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[wechat][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->assertRequestAndParams($rocket);

        verify_wechat_sign($rocket->getDestinationOrigin(), $rocket->getParams());

        $body = json_decode($rocket->getDestination()->getBody()->getContents(), true);
        $destination = decrypt_wechat_resource($body['resource'] ?? [], $rocket->getParams());

        $rocket->setDirection(NoHttpRequestParser::class)
            ->setPayload($body)
            ->setDestination(new Collection($destination));

        Logger::info('[wechat][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function assertRequestAndParams(Rocket $rocket): void
    {
        $request = $rocket->getParams()['request'] ?? null;

        if (is_null($request) || !($request instanceof ServerRequestInterface)) {
            throw new InvalidParamsException(InvalidParamsException::REQUEST_NULL_ERROR);
        }

        $rocket->setDestinationOrigin($request);
        $rocket->setDestination($request);
        $rocket->setParams($rocket->getParams()['params'] ?? []);
    }
}
