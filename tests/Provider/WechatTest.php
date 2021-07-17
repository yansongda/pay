<?php

namespace Yansongda\Pay\Tests\Provider;

use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\LaunchPlugin;
use Yansongda\Pay\Plugin\Wechat\PreparePlugin;
use Yansongda\Pay\Plugin\Wechat\SignPlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;

class WechatTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(InvalidParamsException::SHORTCUT_NOT_FOUND);

        Pay::wechat()->foo();
    }

    public function testShortcutIncompatible()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(InvalidParamsException::SHORTCUT_NOT_FOUND);

        Pay::wechat()->foo();
    }

    public function testMergeCommonPlugins()
    {
        Pay::config([]);
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [PreparePlugin::class],
            $plugins,
            [SignPlugin::class],
            [LaunchPlugin::class, ParserPlugin::class],
        ), Pay::wechat()->mergeCommonPlugins($plugins));
    }
}
