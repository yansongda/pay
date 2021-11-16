<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Ecommerce\Refund;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund\ApplyPlugin;
use Yansongda\Pay\Provider\Wechat;
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

    public function testNormal()
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
        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/ecommerce/refunds/apply'), $radar->getUri());
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

    public function testPartnerDirectPayload()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'test' => 'yansongda',
            'sub_mchid' => '123',
            'sp_appid' => '456',
        ]));

        $plugin = new ApplyPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

        $payload = $result->getPayload();
        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/ecommerce/refunds/apply'), $radar->getUri());
        self::assertEquals('456', $payload->get('sp_appid'));
        self::assertEquals('123', $payload->get('sub_mchid'));
        self::assertEquals('yansongda', $payload->get('test'));
        self::assertCount(4, $payload->all());
    }

    public function testPartnerType()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider', '_type' => 'mini'])->setPayload(new Collection([
            'test' => 'yansongda',
        ]));

        $plugin = new ApplyPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

        $payload = $result->getPayload();
        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/ecommerce/refunds/apply'), $radar->getUri());
        self::assertEquals('wx55955316af4ef14', $payload->get('sp_appid'));
        self::assertEquals('1600314070', $payload->get('sub_mchid'));
        self::assertEquals('yansongda', $payload->get('test'));
        self::assertCount(4, $payload->all());
    }
}
