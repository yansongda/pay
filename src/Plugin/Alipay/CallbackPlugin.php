<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidResponseException;
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
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->filterPayload($rocket);

        if (!($rocket->getParams()['sign'] ?? false)) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_SIGN, '', $rocket->getParams());
        }

        verify_alipay_sign($rocket->getParams(), $this->getSignContent($rocket->getPayload()), base64_decode($rocket->getParams()['sign']));

        $rocket->setDirection(NoHttpRequestParser::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[alipay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function filterPayload(Rocket $rocket): void
    {
        $payload = (new Collection($rocket->getParams()))->filter(function ($v, $k) {
            return '' !== $v && !is_null($v) && 'sign' != $k && 'sign_type' != $k;
        });

        $rocket->setPayload($payload);
    }

    protected function getSignContent(Collection $payload): string
    {
        return urldecode($payload->sortKeys()->toString());
    }
}
