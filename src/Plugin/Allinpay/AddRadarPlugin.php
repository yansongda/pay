<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Allinpay;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AllinpayTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;
use function Yansongda\Artful\get_radar_method;

/**
 * @see https://prodoc.allinpay.com/doc/256/ 通联 apiweb 接口以 POST + application/x-www-form-urlencoded 方式交互
 */
class AddRadarPlugin implements PluginInterface
{
    use AllinpayTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Allinpay][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var AllinpayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALLINPAY, $params);
        $payload = $rocket->getPayload();

        $rocket->setRadar(new Request(
            get_radar_method(new Collection($params)) ?? 'POST',
            self::getAllinpayUrl($config, $payload),
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'User-Agent' => 'yansongda/pay-v3',
            ],
            filter_params($payload)->query(),
        ));

        Logger::info('[Allinpay][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
