<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Douyin;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\InvokePlugin;

/**
 * 小程序下单，返回前端调起参数（`data` + `byteAuthorization`），供前端 `tt.requestOrder` 调起支付.
 */
class MiniShortcut implements ShortcutInterface
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
            InvokePlugin::class,
        ];
    }
}
