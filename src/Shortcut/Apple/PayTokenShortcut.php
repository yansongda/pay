<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Apple;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\PayTokenPlugin;

/**
 * Apple Pay 支付令牌本地验签解密（无 AddRadar/Response/Parser，不发 HTTP 请求）。
 */
class PayTokenShortcut implements ShortcutInterface
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
            PayTokenPlugin::class,
        ];
    }
}
