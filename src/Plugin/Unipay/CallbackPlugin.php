<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\verify_unipay_sign;

use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class CallbackPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[unipay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->formatPayload($rocket);
        $signature = $rocket->getParams()['signature'] ?? false;

        if (!$signature) {
            throw new InvalidResponseException(Exception::INVALID_RESPONSE_SIGN, '', $rocket->getParams());
        }

        verify_unipay_sign($rocket->getParams(), $this->getSignContent($rocket->getPayload()), base64_decode($signature));

        $rocket->setDirection(NoHttpRequestParser::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[unipay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function formatPayload(Rocket $rocket): void
    {
        $payload = (new Collection($rocket->getParams()))
            ->filter(fn ($v, $k) => 'signature' != $k && !Str::startsWith($k, '_'));

        $rocket->setPayload($payload);
    }

    protected function getSignContent(Collection $payload): string
    {
        return $payload->sortKeys()->toString();
    }
}
