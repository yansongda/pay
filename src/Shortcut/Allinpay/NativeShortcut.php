<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Allinpay;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Allinpay\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Allinpay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Allinpay\Pay\NativePlugin;
use Yansongda\Pay\Plugin\Allinpay\ResponsePlugin;
use Yansongda\Pay\Plugin\Allinpay\StartPlugin;
use Yansongda\Pay\Plugin\Allinpay\VerifySignaturePlugin;

class NativeShortcut implements ShortcutInterface
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
            NativePlugin::class,
            AddPayloadSignPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
