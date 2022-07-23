<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Mini;

class InvokePrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayPlugin
{
    protected function getConfigKey(): string
    {
        return 'mini_app_id';
    }
}
