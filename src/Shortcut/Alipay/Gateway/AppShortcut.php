<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Alipay\Gateway;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\Pay\App\InvokePayPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\ResponseInvokeStringPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\StartPlugin;

class AppShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            StartPlugin::class,
            InvokePayPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            ResponseInvokeStringPlugin::class,
            ParserPlugin::class,
        ];
    }
}
