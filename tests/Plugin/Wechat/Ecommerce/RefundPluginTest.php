<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Ecommerce;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund\RefundPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RefundPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection());

        $plugin = new RefundPlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::SERVICE_NOT_FOUND_ERROR);

        $plugin->assembly($rocket, function ($rocket) {return $rocket;});
    }

    public function testPartner()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection());

        $plugin = new RefundPlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) {return $rocket;});
    }

    public function testPartnerNoTransactionId()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'out_refund_no' => '456',
            'amount' => [
                'refund' => 1,
                'total' => 1,
                'currency' => 'CNY',
            ]]));

        $plugin = new RefundPlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) {return $rocket;});
    }

    public function testPartnerNoOutRefundNo()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'transaction_id' => '123',
            'amount' => [
                'refund' => 1,
                'total' => 1,
                'currency' => 'CNY',
            ],
        ]));

        $plugin = new RefundPlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) {return $rocket;});
    }

    public function testPartnerNoAmount()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'transaction_id' => '123',
            'out_refund_no' => '456',
        ]));

        $plugin = new RefundPlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) {return $rocket;});
    }

    public function testPartnerNoAmountRefund()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'transaction_id' => '123',
            'out_refund_no' => '456',
            'amount' => [
                'total' => 1,
                'currency' => 'CNY',
            ],
        ]));

        $plugin = new RefundPlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) {return $rocket;});
    }

    public function testPartnerNoAmountTotal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'transaction_id' => '123',
            'out_refund_no' => '456',
            'amount' => [
                'refund' => 1,
                'currency' => 'CNY',
            ],
        ]));

        $plugin = new RefundPlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) {return $rocket;});
    }

    public function testPartnerNoAmountCurrency()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'transaction_id' => '123',
            'out_refund_no' => '456',
            'amount' => [
                'refund' => 1,
                'total' => 1,
            ],
        ]));

        $plugin = new RefundPlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) {return $rocket;});
    }

    public function testPartnerDirectPayload()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'transaction_id' => '123',
            'out_refund_no' => '456',
            'amount' => [
                'refund' => 1,
                'total' => 1,
                'currency' => 'CNY',
            ],
        ]));

        $plugin = new RefundPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) {return $rocket;});

        $payload = $result->getPayload();

        self::assertEquals('wx55955316af4ef14', $payload->get('sp_appid'));
        self::assertEquals('1600314070', $payload->get('sub_mchid'));
        self::assertEquals('123', $payload->get('transaction_id'));
        self::assertEquals('456', $payload->get('out_refund_no'));
        self::assertEquals([
            'refund' => 1,
            'total' => 1,
            'currency' => 'CNY',
        ], $payload->get('amount'));
    }
}
