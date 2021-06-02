<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Shortcut;

use Yansongda\Pay\Plugin\Alipay\Trade\WapPayPlugin;

class WapShortcut extends WebShortcut
{
    public function getPlugins(): array
    {
        return [
            WapPayPlugin::class,
            $this->buildHtmlResponse(),
        ];
    }
}
