<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Traits\UnipayTrait;

use function Yansongda\Artful\get_radar_method;

class AddRadarPlugin implements PluginInterface
{
    use UnipayTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var UnipayConfig $config */
        $config = self::getProviderConfig('unipay', $params);
        $payload = $rocket->getPayload();

        $rocket->setRadar(new Request(
            get_radar_method($payload) ?? 'POST',
            self::getUnipayUrl($config, $payload),
            $this->getHeaders(),
            self::getUnipayBody($payload),
        ));

        Logger::info('[Unipay][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getHeaders(): array
    {
        return [
            'User-Agent' => 'yansongda/pay-v3',
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
        ];
    }
}
