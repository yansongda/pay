<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ClosePluginTest extends TestCase
{
    protected ClosePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ClosePlugin();
    }

    public function testNormal(): void
    {
        $params = [
            '_config' => 'alipay-v3',
            'out_trade_no' => 'yansongda-2026',
            'notify_url' => 'https://pay.yansongda.cn/alipay/notify',
        ];
        $rocket = (new Rocket())->setParams($params)->setPayload(new Collection($params));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/v3/alipay/trade/close', $result->getPayload()->get('_url'));

        // 调用方传入的 notify_url 优先保留
        self::assertEquals('https://pay.yansongda.cn/alipay/notify', $result->getPayload()->get('notify_url'));
        self::assertEquals('yansongda-2026', $result->getPayload()->get('out_trade_no'));
    }

    public function testNotifyUrlFallbackToConfig(): void
    {
        // 调用方未传 notify_url 时回落租户配置（官方 AlipayTradeCloseModel 独有 notify_url 字段）
        Pay::config([
            'alipay' => [
                'alipay-v3-close' => [
                    'app_id' => 'alipay_v3_test_app_id',
                    'app_secret_cert' => __DIR__.'/../../../Cert/alipay-v3/app_secret_test.pem',
                    'alipay_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAzyN1XQGDxQW7Krn1Fs9vBQ02Ng9TYDMO2tBlaPZw8GUDljlD1fL3xxQT04shbVvDK2R28e1+JizqeAIaYtrjeMczGyKDwHhGYp9atMFCiUaE+IGb4IRzgZa9DZlv0W3PCULkL0Fuot1E/OsGeCX8Ny4ZWrj+KEhNg7A40M4RAkingvi47CxLYVHHyi59OkpXzGFR5gdqv0oFCUgFUp6QREnW3IOye8WCeJB1siWHRLvhUP9FP0h2sBbgcd/nKVakE0Ger7RySCUZI6Oap1oYNAH9Vnt4aeDJ9OIw47O3rQzVgn+6IGtupZ5aG6Z5CRgleII03HE681o3wrcpYX5XwQIDAQAB',
                    'version' => 'v3',
                    'notify_url' => 'https://pay.yansongda.cn/alipay/close-notify',
                ],
            ],
            '_force' => true,
        ]);

        $params = ['_config' => 'alipay-v3-close', 'out_trade_no' => 'yansongda-2026'];
        $rocket = (new Rocket())->setParams($params)->setPayload(new Collection($params));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('https://pay.yansongda.cn/alipay/close-notify', $result->getPayload()->get('notify_url'));
    }

    public function testNotifyUrlMissing(): void
    {
        // 调用方未传且租户未配置：notify_url 为 null（alipay-v3 测试租户无 notify_url 配置）
        $params = ['_config' => 'alipay-v3', 'out_trade_no' => 'yansongda-2026'];
        $rocket = (new Rocket())->setParams($params)->setPayload(new Collection($params));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertNull($result->getPayload()->get('notify_url'));
    }
}
