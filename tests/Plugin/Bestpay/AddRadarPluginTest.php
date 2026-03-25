<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Bestpay\V1\AddRadarPlugin;
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
        $rocket = new Rocket();
        $rocket->setParams([])
            ->setPayload(new Collection([
                '_url' => 'pay/cashierPay',
                '_method' => 'POST',
                'merchantNo' => 'bestpay_merchant_no',
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertStringContainsString('pay/cashierPay', (string) $result->getRadar()->getUri());
        self::assertEquals('POST', $result->getRadar()->getMethod());
        self::assertEquals('application/json; charset=utf-8', $result->getRadar()->getHeaderLine('Content-Type'));
        self::assertEquals('yansongda/pay-v3', $result->getRadar()->getHeaderLine('User-Agent'));
    }

    public function testSandboxUrl(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([])
            ->setPayload(new Collection([
                '_url' => 'pay/cashierPay',
                '_method' => 'POST',
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertStringStartsWith('https://sandbox.bestpay.com.cn/', (string) $result->getRadar()->getUri());
    }

    public function testMissingUrl(): void
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_BESTPAY_URL_MISSING);

        $rocket = new Rocket();
        $rocket->setParams([])
            ->setPayload(new Collection(['merchantNo' => 'test']));

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
