<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Jsb;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Traits\JsbTrait;
use Yansongda\Supports\Collection;

class AddRadarPlugin implements PluginInterface
{
    use JsbTrait;

    /**
     * @throws ServiceNotFoundException
     * @throws ContainerException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Jsb][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var JsbConfig $config */
        $config = self::getProviderConfig('jsb', $params);
        $payload = $rocket->getPayload();

        $rocket->setRadar(new Request(
            strtoupper($params['_method'] ?? 'POST'),
            self::getJsbUrl($config, $payload),
            $this->getHeaders(),
            $this->getBody($payload),
        ));

        Logger::info('[Jsb][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'text/html',
            'User-Agent' => 'yansongda/pay-v3',
        ];
    }

    protected function getBody(Collection $payload): string
    {
        $sign = $payload->get('sign');
        $signType = $payload->get('signType');

        $payload->forget('sign');
        $payload->forget('signType');

        $payload = $payload->sortKeys();

        $payload->set('sign', $sign);
        $payload->set('signType', $signType);

        return $payload->toString();
    }
}
