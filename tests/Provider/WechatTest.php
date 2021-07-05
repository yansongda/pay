<?php

namespace Yansongda\Pay\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\LaunchPlugin;
use Yansongda\Pay\Plugin\Wechat\PreparePlugin;
use Yansongda\Pay\Plugin\Wechat\SignPlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;

class WechatTest extends TestCase
{
    protected function setUp(): void
    {
        $config = [
            'wechat' => [
                'default' => [
                    // 公众号 的 app_id
                    'mp_app_id' => '2016082000291234',
                    // 小程序 的 app_id
                    'mini_app_id' => '',
                    // app 的 app_id
                    'app_id' => '',
                    // 商户号
                    'mch_id' => '',
                    // 合单 app_id
                    'combine_app_id' => '',
                    // 合单商户号
                    'combine_mch_id' => '',
                    // 商户秘钥
                    'mch_secret_key' => '',
                    // 商户私钥
                    'mch_secret_cert' => '',
                    // 商户公钥证书路径
                    'mch_public_cert_path' => '',
                    // 微信公钥证书路径
                    'wechat_public_cert_path' => '',
                    'mode' => Pay::MODE_SANDBOX,],
            ]
        ];
        Pay::config($config);
    }

    protected function tearDown(): void
    {
        Pay::clear();
    }

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
