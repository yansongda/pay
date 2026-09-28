<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Apple\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\RefundPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RefundPluginTest extends TestCase
{
    protected RefundPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertStringContainsString('inApps/v2/refund/lookup/', $payload->get('_url'));
        self::assertStringContainsString('tx_test_456', $payload->get('_url'));
        self::assertFalse($payload->has('transaction_id'));
    }

    public function testUrlEncodedTransactionId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx id/with 特殊字符']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertStringContainsString('/inApps/v2/refund/lookup/'.urlencode('tx id/with 特殊字符'), $payload->get('_url'));
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

    public function testSignedTransactionsPassedThroughUntouched()
    {
        // 一期设计决策：退款历史响应中的 signedTransactions（内嵌 JWS）不做自动验签，数组原样透传给调用方
        $signedTransactions = [
            'eyJhbGciOiJFUzI1NiJ9.eyJ0cmFuc2FjdGlvbklkIjoidHhfc2lnbmVkXzEifQ.sig1',
            'eyJhbGciOiJFUzI1NiJ9.eyJ0cmFuc2FjdGlvbklkIjoidHhfc2lnbmVkXzIifQ.sig2',
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', 'signedTransactions' => $signedTransactions]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertSame($signedTransactions, $payload->get('signedTransactions'));
    }

    public function testMissingTransactionId()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

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
