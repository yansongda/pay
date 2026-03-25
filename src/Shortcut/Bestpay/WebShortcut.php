<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Bestpay;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\Pay\Web\PayPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\ResponsePlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin;

class WebShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadSignPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
