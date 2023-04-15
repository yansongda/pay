<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Plugin\Wechat\Papay\OnlyContractPlugin;
use Yansongda\Pay\Plugin\Wechat\PreparePlugin;

/**
 * 返回只签约（委托代扣）参数.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter3_3.shtml
 */
class PapayContractShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            PreparePlugin::class,
            OnlyContractPlugin::class,
        ];
    }
}
