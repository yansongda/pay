<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
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

    public function testNormal(): void
    {
        $body = '{"out_trade_no":"yansongda","total_amount":"0.01"}';
        $rocket = (new Rocket())
            ->setParams(['_config' => 'alipay-v3'])
            ->setPayload(new Collection([
                '_method' => 'POST',
                '_url' => '/v3/alipay/trade/pay',
                '_body' => $body,
                '_authorization' => 'ALIPAY-SHA256withRSA app_id=alipay_v3_test_app_id,nonce=test,timestamp=123,sign=abc',
                '_app_auth_token' => 'test_token',
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('https://openapi.alipay.com/v3/alipay/trade/pay', $radar->getUri()->__toString());
        self::assertEquals($body, $radar->getBody()->getContents());

        $headers = $radar->getHeaders();
        self::assertEquals('ALIPAY-SHA256withRSA app_id=alipay_v3_test_app_id,nonce=test,timestamp=123,sign=abc', $headers['Authorization'][0]);
        self::assertEquals('application/json; charset=utf-8', $headers['Content-Type'][0]);
        self::assertEquals('application/json, text/plain, application/x-gzip', $headers['Accept'][0]);
        self::assertEquals('yansongda/pay-v3', $headers['User-Agent'][0]);
        self::assertEquals('test_token', $headers['alipay-app-auth-token'][0]);

        // alipay-request-id 每请求必带且为 UUID
        self::assertArrayHasKey('alipay-request-id', $headers);
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $headers['alipay-request-id'][0]
        );
    }

    public function testWithoutAppAuthToken(): void
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'alipay-v3'])
            ->setPayload(new Collection([
                '_method' => 'POST',
                '_url' => '/v3/alipay/trade/pay',
                '_body' => '{"out_trade_no":"yansongda"}',
                '_authorization' => 'ALIPAY-SHA256withRSA app_id=alipay_v3_test_app_id,nonce=test,timestamp=123,sign=abc',
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $headers = $result->getRadar()->getHeaders();
        self::assertArrayHasKey('Authorization', $headers);
        self::assertArrayNotHasKey('alipay-app-auth-token', $headers);
    }

    public function testBodyExcludeUnderscoreKeys(): void
    {
        // 模拟真实管道: AddPayloadBodyPlugin 通过 filter_params 生成 `_body`,`_` 前缀键不进 body
        $rocket = (new Rocket())
            ->setParams(['_config' => 'alipay-v3'])
            ->setPayload(new Collection([
                '_method' => 'POST',
                '_url' => '/v3/alipay/trade/pay',
                '_authorization' => 'ALIPAY-SHA256withRSA app_id=alipay_v3_test_app_id,nonce=test,timestamp=123,sign=abc',
                '_app_auth_token' => 'test_token',
                'out_trade_no' => 'yansongda',
                'total_amount' => '0.01',
            ]));

        $rocket = (new AddPayloadBodyPlugin())->assembly($rocket, fn ($rocket) => $rocket);
        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $bodyJson = json_decode($result->getRadar()->getBody()->getContents(), true);

        self::assertEquals(['out_trade_no' => 'yansongda', 'total_amount' => '0.01'], $bodyJson);
        foreach (array_keys($bodyJson) as $key) {
            self::assertStringStartsNotWith('_', (string) $key);
        }
    }
}
