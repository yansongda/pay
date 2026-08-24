<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Stripe;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\PayPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;

class IntentShortcut implements ShortcutInterface
{
    /**
     * @param array<string, mixed> $params
     *
     * @return array<class-string>
     */
    public function getPlugins(array $params): array
    {
        return [
            StartPlugin::class,
            PayPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
