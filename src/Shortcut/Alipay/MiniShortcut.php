<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Alipay;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Plugin\Alipay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Mini\PayPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\ParserPlugin;

class MiniShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            StartPlugin::class,
            PayPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
