<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Order;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\StartDownloadOrderPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class StartDownloadOrderPluginTest extends TestCase
{
    protected StartDownloadOrderPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new StartDownloadOrderPlugin();
    }


    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'start_date' => '2024-01-01 00:00:00',
            'end_date' => '2024-01-01 23:59:59',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('/xpay/start_download_order', $payload->get('_url'));
        self::assertEquals(0, $payload->get('env'));
        self::assertEquals('2024-01-01 00:00:00', $payload->get('start_date'));
        self::assertEquals('2024-01-01 23:59:59', $payload->get('end_date'));
    }

    public function testSandboxEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'env' => 1,
            'start_date' => '2024-01-01 00:00:00',
            'end_date' => '2024-01-01 23:59:59',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(1, $payload->get('env'));
    }

    public function testWithAccessToken()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'start_date' => '2024-01-01 00:00:00',
            'end_date' => '2024-01-01 23:59:59',
            'access_token' => 'test_access_token',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('test_access_token', $payload->get('access_token'));
    }
}
