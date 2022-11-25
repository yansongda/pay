<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Contract\PluginInterface;

use function Yansongda\Pay\decrypt_wechat_resource;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\verify_wechat_sign;

use Yansongda\Supports\Collection;

class CallbackPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[wechat][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->formatRequestAndParams($rocket);

        /* @phpstan-ignore-next-line */
        verify_wechat_sign($rocket->getDestinationOrigin(), $rocket->getParams());

        $body = json_decode((string) $rocket->getDestination()->getBody(), true);

        $rocket->setDirection(NoHttpRequestParser::class)->setPayload(new Collection($body));

        $body['resource'] = decrypt_wechat_resource($body['resource'] ?? [], $rocket->getParams());

        $rocket->setDestination(new Collection($body));

        Logger::info('[wechat][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function formatRequestAndParams(Rocket $rocket): void
    {
        $request = $rocket->getParams()['request'] ?? null;

        if (!($request instanceof ServerRequestInterface)) {
            throw new InvalidParamsException(Exception::REQUEST_NULL_ERROR);
        }

        $rocket->setDestination(clone $request)
            ->setDestinationOrigin($request)
            ->setParams($rocket->getParams()['params'] ?? []);
    }
}
