<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Psr\Http\Message\RequestInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Request;
use Yansongda\Pay\Rocket;

class RadarPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][RadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setRadar($this->getRequest($rocket));

        Logger::info('[alipay][RadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getRequest(Rocket $rocket): RequestInterface
    {
        return new Request(
            $this->getMethod($rocket),
            $this->getUrl($rocket),
            $this->getHeaders(),
            $this->getBody($rocket),
        );
    }

    protected function getMethod(Rocket $rocket): string
    {
        return strtoupper($rocket->getParams()['_method'] ?? 'POST');
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getUrl(Rocket $rocket): string
    {
        $config = get_alipay_config($rocket->getParams());

        return Alipay::URL[$config->get('mode', Pay::MODE_NORMAL)];
    }

    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
    }

    protected function getBody(Rocket $rocket): string
    {
        return $rocket->getPayload()->query();
    }
}
