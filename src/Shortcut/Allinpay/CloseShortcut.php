<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Allinpay;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Allinpay\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Allinpay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Allinpay\ResponsePlugin;
use Yansongda\Pay\Plugin\Allinpay\StartPlugin;
use Yansongda\Pay\Plugin\Allinpay\Tranx\ClosePlugin;
use Yansongda\Pay\Plugin\Allinpay\VerifySignaturePlugin;

class CloseShortcut implements ShortcutInterface
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
            ClosePlugin::class,
            AddPayloadSignPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
