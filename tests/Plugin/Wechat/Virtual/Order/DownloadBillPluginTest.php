<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Order;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\DownloadBillPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class DownloadBillPluginTest extends TestCase
{
    protected DownloadBillPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new DownloadBillPlugin();
    }


    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'bill_date' => '2024-01-01',
            'bill_type' => 'ALL',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('xpay/download_bill', $payload->get('_url'));
        self::assertNull($payload->get('env'));
        self::assertEquals('2024-01-01', $payload->get('bill_date'));
        self::assertEquals('ALL', $payload->get('bill_type'));
    }

    public function testSandboxEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'env' => 1,
            'bill_date' => '2024-01-01',
            'bill_type' => 'ALL',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(1, $payload->get('env'));
    }

    public function testWithAccessToken()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'bill_date' => '2024-01-01',
            'bill_type' => 'ALL',
            'access_token' => 'test_access_token',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('test_access_token', $payload->get('access_token'));
    }
}
