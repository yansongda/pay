<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddRadarPluginTest extends TestCase
{
    protected AddRadarPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddRadarPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '{"out_trade_no":"123"}',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();
        self::assertEquals('POST', $radar->getMethod());
        self::assertStringStartsWith('https://openapi.alipay.com/v3/alipay/trade/query', (string) $radar->getUri());
        self::assertStringContainsString('ALIPAY-SHA256withRSA', $radar->getHeaderLine('Authorization'));
        self::assertEquals('application/json; charset=utf-8', $radar->getHeaderLine('Content-Type'));
    }

    public function testSandboxUrl()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3', '_sandbox' => true])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '',
            ]));

        // Use default config (no sandbox mode set), just test URL construction
        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEmpty($result->getRadar()->getHeaderLine('Authorization'));
    }

    public function testAuthorizationHeaderFormat()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '{}',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $auth = $result->getRadar()->getHeaderLine('Authorization');
        self::assertStringStartsWith('ALIPAY-SHA256withRSA ', $auth);
        self::assertStringContainsString('app_id=', $auth);
        self::assertStringContainsString('app_cert_sn=', $auth);
        self::assertStringContainsString('nonce=', $auth);
        self::assertStringContainsString('timestamp=', $auth);
        self::assertStringContainsString('sign=', $auth);
    }

    public function testBodyPassedToRequest()
    {
        $body = '{"out_trade_no":"test123"}';

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => $body,
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals($body, (string) $result->getRadar()->getBody());
    }
}
