<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Stubs\Plugin;

use Yansongda\Pay\Plugin\Wechat\V3\CallbackPlugin;

class CallbackPluginStub extends CallbackPlugin
{
    protected static function verifyWechatTimestamp(int $timestamp): void
    {
    }
}
