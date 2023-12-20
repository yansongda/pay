<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Plugin\Alipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\AddSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\FormatBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\App\PayPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponseInvokeStringPlugin;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\ParserPlugin;

class AppShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            StartPlugin::class,
            PayPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponseInvokeStringPlugin::class,
            ParserPlugin::class,
        ];
    }
}
