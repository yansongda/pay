<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Mini;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_5_4.shtml
 */
class InvokePrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayPlugin
{
    protected function getConfigKey(): string
    {
        return 'mini_app_id';
    }
}
