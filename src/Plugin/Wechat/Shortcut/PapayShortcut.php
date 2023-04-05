<?php

namespace Yansongda\Pay\Plugin\Wechat\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\Papay\PayContractOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayV2Plugin;
use Yansongda\Pay\Plugin\Wechat\PreparePlugin;
use Yansongda\Pay\Plugin\Wechat\RadarSignPlugin;

/**
 * 支付中签约.
 */
class PapayShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            PreparePlugin::class,
            PayContractOrderPlugin::class,
            RadarSignPlugin::class,
            InvokePrepayV2Plugin::class,
            ParserPlugin::class,
        ];
    }
}
