<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Request;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_alipay_config;

class AddRadarPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        $rocket->setRadar(new Request(
            $this->getMethod($params),
            $this->getUrl($params),
            $this->getHeaders(),
            $this->getBody($rocket->getPayload()),
        ));

        Logger::info('[Alipay][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getMethod(array $params): string
    {
        return strtoupper($params['_method'] ?? 'POST');
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function getUrl(array $params): string
    {
        $config = get_alipay_config($params);

        return Alipay::URL[$config['mode'] ?? Pay::MODE_NORMAL];
    }

    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'User-Agent' => 'yansongda/pay-v3',
        ];
    }

    protected function getBody(Collection $payload): string
    {
        return $payload->query();
    }
}
