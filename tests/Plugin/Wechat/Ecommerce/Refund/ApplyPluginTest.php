<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Ecommerce\Refund;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund\ApplyPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ApplyPluginTest extends TestCase
{
    public function testNotInServiceMode()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection());

        $plugin = new ApplyPlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::NOT_IN_SERVICE_MODE);

        $plugin->assembly($rocket, function ($rocket) {return $rocket; });
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

        $plugin = new ApplyPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('wx55955316af4ef13', $payload->get('sp_appid'));
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
