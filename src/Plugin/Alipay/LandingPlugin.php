<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class LandingPlugin implements PluginInterface
{
    /**
     * @return \Yansongda\Supports\Collection|\Symfony\Component\HttpFoundation\Response
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        $this->verifySign($rocket);

        $response = $rocket->getDestination()->get($this->getResponseKey($rocket));

        $rocket->setDestination(Collection::wrap($response));

        return $rocket;
    }

    protected function verifySign(Rocket $rocket): void
    {
        // todo
    }

    protected function getResponseKey(Rocket $rocket): string
    {
        $method = $rocket->getPayload()->get('method');

        return str_replace('.', '_', $method);
    }
}
