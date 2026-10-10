<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Apple\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\QueryHistoryPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryHistoryPluginTest extends TestCase
{
    protected QueryHistoryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryHistoryPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertStringContainsString('/inApps/v2/history/', $payload->get('_url'));
        self::assertStringContainsString('tx_test_456', $payload->get('_url'));
        self::assertFalse($payload->has('transaction_id'));
    }

    public function testUrlEncodedTransactionId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx id/with 特殊字符']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertStringContainsString('/inApps/v2/history/'.urlencode('tx id/with 特殊字符'), $payload->get('_url'));
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

    public function testMissingTransactionId()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: Apple 查询交易历史，缺少 transaction_id 参数');

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
