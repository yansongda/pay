<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Request;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_radar_body;
use function Yansongda\Pay\get_radar_method;
use function Yansongda\Pay\get_unipay_config;
use function Yansongda\Pay\get_unipay_url;

class AddRadarPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_unipay_config($params);
        $payload = $rocket->getPayload();

        $rocket->setRadar(new Request(
            get_radar_method($payload) ?? 'POST',
            get_unipay_url($config, $payload),
            $this->getHeaders($payload),
            get_radar_body($payload),
        ));

        Logger::info('[Unipay][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getHeaders(?Collection $payload): array
    {
        $headers = [
            'User-Agent' => 'yansongda/pay-v3',
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
        ];

        if ($payload->has('_content-type')) {
            $headers['Content-Type'] = $payload->get('_content-type');
        }

        if ($payload->has('_accept')) {
            $headers['Accept'] = $payload->get('_accept');
        }

        return $headers;
    }
}
