<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Douyin;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ObtainClientTokenPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Refund\RefundPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ResponsePlugin;

class TradeRefundShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            RefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
