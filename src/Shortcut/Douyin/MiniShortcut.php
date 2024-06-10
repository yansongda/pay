<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Douyin;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Douyin\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\ResponsePlugin;
use Yansongda\Pay\Plugin\Douyin\V1\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\Mini\PayPlugin;

class MiniShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
