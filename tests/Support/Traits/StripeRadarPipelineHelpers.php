<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;

/**
 * Stripe 管道 Helper。
 *
 * 手动执行 `StartPlugin → 业务插件 → AddRadarPlugin` 片段，用于断言 AddRadarPlugin 注入的 UA/请求头。
 */
trait StripeRadarPipelineHelpers
{
    /**
     * @param array<string, mixed> $params
     * @param array<class-string>  $businessPlugins
     *
     * @throws InvalidParamsException
     */
    protected function runPipeline(array $params, array $businessPlugins): Rocket
    {
        $rocket = new Rocket();
        $rocket->setParams($params);

        foreach ([StartPlugin::class, ...$businessPlugins, AddRadarPlugin::class] as $plugin) {
            $rocket = (new $plugin())->assembly($rocket, static fn ($rocket) => $rocket);
        }

        return $rocket;
    }
}
