<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Plugin\Wechat\Fund\Transfer\CreatePlugin;

class TransferShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            CreatePlugin::class,
        ];
    }
}
