<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\PrecreatePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PrecreatePluginTest extends TestCase
{
    protected PrecreatePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PrecreatePlugin();
    }

    public function testEmptyPayload(): void
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 扫码支付（Precreate），参数为空');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'alipay-v3'])->setPayload(new Collection([
            'out_trade_no' => '2023122621450002',
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - V3 Scan',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals([
            'out_trade_no' => '2023122621450002',
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - V3 Scan',
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/precreate',
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

                    'app_public_cert_path' => __DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt',
                    'alipay_public_cert_path' => __DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt',
                    'notify_url' => 'https://pay.yansongda.cn/v3/notify',
                ],
            ],
            '_force' => true,
        ]);

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'alipay-v3-notify'])->setPayload(new Collection([
            'out_trade_no' => '2023122621450002',
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - V3 Scan',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/trade/precreate', $result->getPayload()->get('_url'));
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

                    'app_public_cert_path' => __DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt',
                    'alipay_public_cert_path' => __DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt',
                    'notify_url' => 'https://pay.yansongda.cn/v3/notify',
                ],
            ],
            '_force' => true,
        ]);

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'alipay-v3-notify'])->setPayload(new Collection([
            'out_trade_no' => '2023122621450002',
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - V3 Scan',
            'notify_url' => 'https://pay.yansongda.cn/override',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame('https://pay.yansongda.cn/override', $result->getPayload()->get('notify_url'));
    }
}
