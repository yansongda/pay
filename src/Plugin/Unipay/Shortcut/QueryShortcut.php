<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\QueryPlugin;

class QueryShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            QueryPlugin::class,
        ];
    }
}
