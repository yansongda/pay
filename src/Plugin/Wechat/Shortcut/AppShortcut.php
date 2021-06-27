<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Plugin\Wechat\Pay\App\InvokePrepayPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\App\PrepayPlugin;

class AppShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            PrepayPlugin::class,
            InvokePrepayPlugin::class,
        ];
    }
}
