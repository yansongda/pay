<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Rocket;

class RadarPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     */
    public function assembly(Rocket $rocket, Closure $next)
    {
        $config = get_alipay_config($rocket->getParams());

        return $next($rocket->setRadar(
            $config['mode'] ?? Alipay::URL[Pay::MODE_NORMAL]
        ));
    }
}
