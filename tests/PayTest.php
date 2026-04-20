<?php

namespace Yansongda\Pay\Tests;

use Hyperf\Pimple\ContainerFactory;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Pay\Config;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Jsb;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Provider\Wechat;

class PayTest extends TestCase
{
    protected function setUp(): void
    {
        Pay::clear();
    }

    protected function tearDown(): void
    {
        Pay::clear();
    }

    public function testConfig()
    {
        $result = Pay::config(['name' => 'yansongda']);
        self::assertTrue($result);
        self::assertEquals('yansongda', Pay::get(ConfigInterface::class)->get('name'));
        self::assertInstanceOf(Alipay::class, Pay::get('alipay'));
        self::assertInstanceOf(Alipay::class, Pay::get(Alipay::class));
        self::assertInstanceOf(Wechat::class, Pay::get('wechat'));
        self::assertInstanceOf(Wechat::class, Pay::get(Wechat::class));
        self::assertInstanceOf(Unipay::class, Pay::get('unipay'));
        self::assertInstanceOf(Unipay::class, Pay::get(Unipay::class));
        self::assertInstanceOf(Jsb::class, Pay::get('jsb'));
        self::assertInstanceOf(Jsb::class, Pay::get(Jsb::class));

        // force
        $result1 = Pay::config(['name' => 'yansongda1', '_force' => true]);
        self::assertTrue($result1);
        self::assertEquals('yansongda1', Pay::get(ConfigInterface::class)->get('name'));
    }

    public function testConfigSkipsPartialProviderValidationWithoutForce(): void
    {
        Pay::config([
            'wechat' => [
                'default' => [
                    'mch_id' => '1600314069',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => __DIR__.'/Cert/wechatAppPrivateKey.pem',
                    'mch_public_cert_path' => __DIR__.'/Cert/wechatAppPublicKey.pem',
                    'notify_url' => 'https://pay.yansongda.cn/original',
                    'mp_app_id' => 'wx-original',
                ],
            ],
        ]);

        $result = Pay::config([
            'wechat' => [
                'default' => [
                    'mp_app_id' => 'wx-updated',
                ],
            ],
        ]);

        $wechatConfig = Pay::get(ConfigInterface::class)->get('wechat.default');
        $typedWechatConfig = Pay::get(Config::class)->getProviderConfig('wechat');

        self::assertFalse($result);
        self::assertIsArray($wechatConfig);
        self::assertSame('wx-original', $wechatConfig['mp_app_id']);
        self::assertInstanceOf(WechatConfig::class, $typedWechatConfig);
        self::assertSame('wx-original', $typedWechatConfig->getMpAppId());
        self::assertSame('1600314069', $typedWechatConfig->getMchId());
        self::assertSame('https://pay.yansongda.cn/original', $typedWechatConfig->getNotifyUrl());
    }

    public function testConfigMergesProviderPartialOverrideWithForce(): void
    {
        Pay::config([
            'wechat' => [
                'default' => [
                    'mch_id' => '1600314069',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => __DIR__.'/Cert/wechatAppPrivateKey.pem',
                    'mch_public_cert_path' => __DIR__.'/Cert/wechatAppPublicKey.pem',
                    'notify_url' => 'https://pay.yansongda.cn/original',
                    'mp_app_id' => 'wx-original',
                ],
            ],
        ]);

        $result = Pay::config([
            '_force' => true,
            'wechat' => [
                'default' => [
                    'mp_app_id' => 'wx-updated',
                ],
            ],
        ]);

        $wechatConfig = Pay::get(ConfigInterface::class)->get('wechat.default');
        $typedWechatConfig = Pay::get(Config::class)->getProviderConfig('wechat');

        self::assertTrue($result);
        self::assertIsArray($wechatConfig);
        self::assertSame('wx-updated', $wechatConfig['mp_app_id']);
        self::assertInstanceOf(WechatConfig::class, $typedWechatConfig);
        self::assertSame('wx-updated', $typedWechatConfig->getMpAppId());
        self::assertSame('1600314069', $typedWechatConfig->getMchId());
        self::assertSame('https://pay.yansongda.cn/original', $typedWechatConfig->getNotifyUrl());
    }

    public function testDirectCallStatic()
    {
        Pay::config();
        $pay = Pay::alipay();
        self::assertInstanceOf(Alipay::class, $pay);

        if (class_exists(ContainerFactory::class)) {
            Pay::clear();
            $container3 = (new ContainerFactory())();
            $pay = Pay::alipay([], $container3);

            self::assertSame($container3, Artful::getContainer());
            self::assertInstanceOf(Alipay::class, $pay);
        }
    }

    public function testSetAndGet()
    {
        Pay::config(['name' => 'yansongda']);

        Pay::set('age', 28);

        self::assertEquals(28, Pay::get('age'));
    }

    public function testConfigWithForceFallsBackWhenCurrentTypedConfigUnavailable(): void
    {
        if (!class_exists(ContainerFactory::class)) {
            self::markTestSkipped('ContainerFactory not available.');
        }

        Pay::clear();
        Pay::setContainer((new ContainerFactory())());

        $result = Pay::config([
            '_force' => true,
            'age' => 28,
        ]);

        self::assertTrue($result);
        self::assertSame(28, Pay::get(ConfigInterface::class)->get('age'));
    }

    public function testMagicCallNotFoundService()
    {
        self::expectException(ServiceNotFoundException::class);

        Pay::foo1([]);
    }
}
