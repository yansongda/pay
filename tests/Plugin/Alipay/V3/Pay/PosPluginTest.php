<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\PosPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PosPluginTest extends TestCase
{
    protected PosPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PosPlugin();
    }

    public function testEmptyPayload(): void
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 付款码支付（Pos），参数为空');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'alipay-v3'])->setPayload(new Collection([
            'out_trade_no' => '2023122621450001',
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - V3 Pos',
            'scene' => 'bar_code',
            'auth_code' => '286958267789018980',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals([
            'out_trade_no' => '2023122621450001',
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - V3 Pos',
            'scene' => 'bar_code',
            'auth_code' => '286958267789018980',
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/pay',
            'notify_url' => null,
        ], $result->getPayload()->all());
    }

    public function testNotifyUrlFromConfig(): void
    {
        // 注册带 notify_url 的临时 v3 租户（公钥模式）
        Pay::config([
            'alipay' => [
                'alipay-v3-notify' => [
                    'app_id' => 'alipay_v3_test_app_id',
                    'app_secret_cert' => __DIR__.'/../../../../Cert/alipay-v3/app_secret_test.pem',
                    'alipay_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAzyN1XQGDxQW7Krn1Fs9vBQ02Ng9TYDMO2tBlaPZw8GUDljlD1fL3xxQT04shbVvDK2R28e1+JizqeAIaYtrjeMczGyKDwHhGYp9atMFCiUaE+IGb4IRzgZa9DZlv0W3PCULkL0Fuot1E/OsGeCX8Ny4ZWrj+KEhNg7A40M4RAkingvi47CxLYVHHyi59OkpXzGFR5gdqv0oFCUgFUp6QREnW3IOye8WCeJB1siWHRLvhUP9FP0h2sBbgcd/nKVakE0Ger7RySCUZI6Oap1oYNAH9Vnt4aeDJ9OIw47O3rQzVgn+6IGtupZ5aG6Z5CRgleII03HE681o3wrcpYX5XwQIDAQAB',
                    'notify_url' => 'https://pay.yansongda.cn/v3/notify',
                    'version' => 'v3',
                ],
            ],
            '_force' => true,
        ]);

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'alipay-v3-notify'])->setPayload(new Collection([
            'out_trade_no' => '2023122621450001',
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - V3 Pos',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/trade/pay', $result->getPayload()->get('_url'));
        self::assertSame('https://pay.yansongda.cn/v3/notify', $result->getPayload()->get('notify_url'));
    }

    public function testNotifyUrlOverrideFromParams(): void
    {
        // 注册带 notify_url 的临时 v3 租户（公钥模式）
        Pay::config([
            'alipay' => [
                'alipay-v3-notify' => [
                    'app_id' => 'alipay_v3_test_app_id',
                    'app_secret_cert' => __DIR__.'/../../../../Cert/alipay-v3/app_secret_test.pem',
                    'alipay_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAzyN1XQGDxQW7Krn1Fs9vBQ02Ng9TYDMO2tBlaPZw8GUDljlD1fL3xxQT04shbVvDK2R28e1+JizqeAIaYtrjeMczGyKDwHhGYp9atMFCiUaE+IGb4IRzgZa9DZlv0W3PCULkL0Fuot1E/OsGeCX8Ny4ZWrj+KEhNg7A40M4RAkingvi47CxLYVHHyi59OkpXzGFR5gdqv0oFCUgFUp6QREnW3IOye8WCeJB1siWHRLvhUP9FP0h2sBbgcd/nKVakE0Ger7RySCUZI6Oap1oYNAH9Vnt4aeDJ9OIw47O3rQzVgn+6IGtupZ5aG6Z5CRgleII03HE681o3wrcpYX5XwQIDAQAB',
                    'notify_url' => 'https://pay.yansongda.cn/v3/notify',
                    'version' => 'v3',
                ],
            ],
            '_force' => true,
        ]);

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'alipay-v3-notify'])->setPayload(new Collection([
            'out_trade_no' => '2023122621450001',
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - V3 Pos',
            'notify_url' => 'https://pay.yansongda.cn/override',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame('https://pay.yansongda.cn/override', $result->getPayload()->get('notify_url'));
    }
}
