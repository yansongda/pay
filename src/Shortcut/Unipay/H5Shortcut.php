<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Unipay;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\H5PayPlugin;
use Yansongda\Pay\Plugin\Unipay\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Unipay\StartPlugin;

class H5Shortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            StartPlugin::class,
            H5PayPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponseHtmlPlugin::class,
            ParserPlugin::class,
        ];
    }
}
