<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Mini;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=7_7&index=5
 */
class InvokePrepayV2Plugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayV2Plugin
{
    protected function getConfigKey(): string
    {
        return 'mini_app_id';
    }
}
