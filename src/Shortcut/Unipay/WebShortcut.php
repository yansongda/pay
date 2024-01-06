<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Unipay;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\AddPayloadBodyPlugin;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\Web\PayPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;

class WebShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponseHtmlPlugin::class,
            ParserPlugin::class,
        ];
    }
}
