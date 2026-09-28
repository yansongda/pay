<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Apple\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\QuerySubscriptionsPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QuerySubscriptionsPluginTest extends TestCase
{
    protected QuerySubscriptionsPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QuerySubscriptionsPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();
        $url = $payload->get('_url');

        self::assertEquals('GET', $payload->get('_method'));
        self::assertStringContainsString('/inApps/v1/subscriptions/', $url);
        self::assertStringContainsString('tx_test_456', $url);
        // Apple 需要重复参数形式，禁止 http_build_query 数组生成的 %5B0%5D 形式
        self::assertStringContainsString('status=1&status=4', $url);
        self::assertStringNotContainsString('%5B', $url);
        self::assertFalse($payload->has('transaction_id'));
    }

    public function testStatusOverride()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => [3]]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();
        $url = $payload->get('_url');

        self::assertStringContainsString('status=3', $url);
        self::assertStringNotContainsString('status=1', $url);
        self::assertStringNotContainsString('%5B', $url);
    }

    public function testStatusOverrideScalar()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => 2]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertStringContainsString('status=2', $payload->get('_url'));
        self::assertStringNotContainsString('%5B', $payload->get('_url'));
    }

    public function testUrlEncodedTransactionId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx id/with 特殊字符']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertStringContainsString('/inApps/v1/subscriptions/'.urlencode('tx id/with 特殊字符'), $payload->get('_url'));
    }

    public function testExceptPayloadKeepsOtherFields()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', 'other' => 'keep_me']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertFalse($payload->has('transaction_id'));
        self::assertEquals('keep_me', $payload->get('other'));
    }

    public function testInvalidStatusThrowsException()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => [0]]));

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNonIntStatusThrowsException()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => ['abc']]));

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingTransactionId()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: Apple 查询订阅状态，缺少 transaction_id 参数');

        $rocket = new Rocket();
        $rocket->setPayload(new Collection([]));

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNullPayloadThrowsException()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $rocket = new Rocket();

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
