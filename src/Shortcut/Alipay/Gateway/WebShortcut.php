<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Alipay\Gateway;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\Pay\Web\HtmlPayPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\StartPlugin;

class WebShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            StartPlugin::class,
            HtmlPayPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponseHtmlPlugin::class,
            ParserPlugin::class,
        ];
    }
}
