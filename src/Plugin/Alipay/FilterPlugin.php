<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Str;

class FilterPlugin implements PluginInterface
{
    /**
     * @return \Yansongda\Supports\Collection|\Symfony\Component\HttpFoundation\Response
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $payload = $rocket->getPayload()->filter(function ($v, $k) {
            return '' !== $v && !is_null($v) && 'sign' != $k;
        });

        $contents = array_filter($payload->get('biz_content'), function ($v, $k) {
            return !Str::startsWith($k, '_');
        }, ARRAY_FILTER_USE_BOTH);

        $rocket->setPayload(
            $payload->merge(['biz_content' => json_encode($contents)])
        );

        return $next($rocket);
    }
}
