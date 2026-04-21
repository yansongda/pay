<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests;

use Hyperf\Pimple\ContainerFactory;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\ServiceNotFoundException;
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

        // Task 2: without force, calling config() again returns false and does not mutate
        $result1 = Pay::config(['name' => 'yansongda1']);
        self::assertFalse($result1);
        self::assertEquals('yansongda', Pay::get(ConfigInterface::class)->get('name'));
    }

    public function testConfigReturnsFalseWithoutForceWhenContainerExists(): void
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

        // Task 2: Without force, config() returns false and does not mutate runtime config
        $result = Pay::config([
            'wechat' => [
                'default' => [
                    'mp_app_id' => 'wx-updated',
                ],
            ],
        ]);

        $wechatConfig = Pay::get(ConfigInterface::class)->get('wechat.default');

        self::assertFalse($result);
        self::assertInstanceOf(WechatConfig::class, $wechatConfig);
        self::assertSame('wx-original', $wechatConfig->getMpAppId());
        self::assertSame('1600314069', $wechatConfig->getMchId());
        self::assertSame('https://pay.yansongda.cn/original', $wechatConfig->getNotifyUrl());
    }

    public function testConfigReplacesRuntimeConfigWithForce(): void
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

        // Task 2: With force, config() replaces runtime config based only on new payload
        $result = Pay::config([
            '_force' => true,
            'wechat' => [
                'default' => [
                    'mp_app_id' => 'wx-updated',
                    'mch_id' => '1600314069',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => __DIR__.'/Cert/wechatAppPrivateKey.pem',
                    'mch_public_cert_path' => __DIR__.'/Cert/wechatAppPublicKey.pem',
                ],
            ],
        ]);

        $wechatConfig = Pay::get(ConfigInterface::class)->get('wechat.default');

        self::assertTrue($result);
        self::assertInstanceOf(WechatConfig::class, $wechatConfig);
        self::assertSame('wx-updated', $wechatConfig->getMpAppId());
        // notify_url was not in the new payload, so it's empty string (default) not preserved from old
        self::assertSame('', $wechatConfig->getNotifyUrl());
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

    public function testMagicCallNotFoundService()
    {
        self::expectException(ServiceNotFoundException::class);

        Pay::foo1([]);
    }

    public function testConfigStoresTypedObjectsInRuntimeConfig(): void
    {
        Pay::config([
            'wechat' => [
                'default' => [
                    'mch_id' => '1600314069',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => __DIR__.'/Cert/wechatAppPrivateKey.pem',
                    'mch_public_cert_path' => __DIR__.'/Cert/wechatAppPublicKey.pem',
                    'notify_url' => 'https://pay.yansongda.cn/notify',
                    'mp_app_id' => 'wx-test',
                ],
            ],
            'logger' => [
                'enable' => false,
                'file' => './logs/wechat.log',
                'level' => 'info',
            ],
            'http' => [
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
            ],
        ]);

        // Task 1 + Task 2: Provider tenant nodes are typed config objects accessible via ConfigInterface
        $wechatConfig = Pay::get(ConfigInterface::class)->get('wechat.default');
        self::assertInstanceOf(WechatConfig::class, $wechatConfig);
        self::assertSame('wx-test', $wechatConfig->getMpAppId());

        // Shared runtime config should remain as plain arrays
        $loggerConfig = Pay::get(ConfigInterface::class)->get('logger');
        $httpConfig = Pay::get(ConfigInterface::class)->get('http');

        self::assertIsArray($loggerConfig);
        self::assertIsArray($httpConfig);
        self::assertSame('./logs/wechat.log', $loggerConfig['file']);
        self::assertSame(5.0, $httpConfig['timeout']);
    }
}
