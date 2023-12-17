<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Plugin\Alipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Alipay\PreparePlugin;
use Yansongda\Pay\Plugin\Alipay\RadarSignPlugin;
use Yansongda\Pay\Plugin\Alipay\Trade\PreCreatePlugin;
use Yansongda\Pay\Plugin\ParserPlugin;

class ScanShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            PreparePlugin::class,
            PreCreatePlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ];
    }
}
