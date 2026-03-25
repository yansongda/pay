<?php

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Trade;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\AddRadarPlugin;
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

    public function testPostWithBody()
    {
        $params = ['_config' => 'trade'];
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'api/trade/v1/create_order',
            '_body' => '{"out_order_no":"123"}',
            '_access_token' => 'test_token_123',
        ]);

        $rocket = (new Rocket())->setParams($params)->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('yansongda/pay-v3', $radar->getHeaderLine('User-Agent'));
        self::assertEquals('application/json; charset=utf-8', $radar->getHeaderLine('Content-Type'));
        self::assertEquals('test_token_123', $radar->getHeaderLine('access-token'));
        self::assertEquals('{"out_order_no":"123"}', (string) $radar->getBody());
        self::assertEquals('POST', $radar->getMethod());
        self::assertStringContainsString('api/trade/v1/create_order', (string) $radar->getUri());
    }

    public function testPostWithPayloadFallback()
    {
        $params = ['_config' => 'trade'];
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'api/trade/v1/create_order',
            '_access_token' => 'test_token_123',
            'out_order_no' => 'order_001',
            'total_amount' => 100,
        ]);

        $rocket = (new Rocket())->setParams($params)->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        $body = json_decode((string) $radar->getBody(), true);
        self::assertEquals('order_001', $body['out_order_no']);
        self::assertEquals(100, $body['total_amount']);
    }

    public function testGetWithQueryString()
    {
        $params = ['_config' => 'trade'];
        $payload = new Collection([
            '_method' => 'GET',
            '_url' => 'api/trade/v1/query_order',
            '_access_token' => 'test_token_456',
            'out_order_no' => 'order_001',
        ]);

        $rocket = (new Rocket())->setParams($params)->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('GET', $radar->getMethod());
        self::assertEmpty((string) $radar->getBody());
        self::assertStringContainsString('out_order_no=order_001', (string) $radar->getUri());
    }

    public function testWithoutAccessToken()
    {
        $params = ['_config' => 'trade'];
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'api/trade/v1/create_order',
            '_body' => '{}',
        ]);

        $rocket = (new Rocket())->setParams($params)->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEmpty($radar->getHeaderLine('access-token'));
    }

    public function testGetWithEmptyParams()
    {
        $params = ['_config' => 'trade'];
        $payload = new Collection([
            '_method' => 'GET',
            '_url' => 'api/trade/v1/query_order',
        ]);

        $rocket = (new Rocket())->setParams($params)->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('GET', $radar->getMethod());
        self::assertStringNotContainsString('?', (string) $radar->getUri());
    }

    public function testPostWithEmptyPayload()
    {
        $params = ['_config' => 'trade'];
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'api/trade/v1/create_order',
        ]);

        $rocket = (new Rocket())->setParams($params)->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEmpty((string) $radar->getBody());
    }
}
