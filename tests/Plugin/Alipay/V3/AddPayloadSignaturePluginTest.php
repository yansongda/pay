<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddPayloadSignaturePluginTest extends TestCase
{
    protected AddPayloadSignaturePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddPayloadSignaturePlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                'app_id' => '9021000122682882',
                'app_cert_sn' => 'test_cert_sn',
                '_method' => 'POST',
                '_url' => '/v3/alipay/trade/query',
                '_body' => ['out_trade_no' => 'test123'],
                '_headers' => ['Accept' => 'application/json'],
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $headers = $result->getPayload()->get('_headers');

        self::assertArrayHasKey('Authorization', $headers);
        self::assertStringStartsWith('ALIPAY-SHA256withRSA app_id=9021000122682882,app_cert_sn=test_cert_sn,nonce=', $headers['Authorization']);
        self::assertArrayHasKey('alipay-request-id', $headers);
    }

    public function testWithAppAuthToken()
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                'app_id' => '9021000122682882',
                'app_cert_sn' => 'test_cert_sn',
                'app_auth_token' => 'auth_token_123',
                '_method' => 'POST',
                '_url' => '/v3/alipay/trade/query',
                '_body' => ['out_trade_no' => 'test123'],
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $headers = $result->getPayload()->get('_headers');

        self::assertEquals('auth_token_123', $headers['alipay-app-auth-token']);
    }
}
