<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Apple;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Apple\AddRadarPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\MerchantSessionPlugin;
use Yansongda\Pay\Plugin\Apple\ResponsePlugin;

/**
 * Apple Pay 网页网关 merchantSession 代理（validation_url 白名单校验后直连 Apple Pay 网关）。
 */
class MerchantSessionShortcut implements ShortcutInterface
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
            MerchantSessionPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
