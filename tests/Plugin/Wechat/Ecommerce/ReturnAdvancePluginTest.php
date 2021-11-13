<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Ecommerce;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund\ReturnAdvancePlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ReturnAdvancePluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection());

        $plugin = new ReturnAdvancePlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::SERVICE_NOT_FOUND_ERROR);

        $plugin->assembly($rocket, function ($rocket) {return $rocket;});
    }

    public function testPartner()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection());

        $plugin = new ReturnAdvancePlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) {return $rocket;});
    }

    public function testPartnerDirectPayload()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection(['refund_id' => '123']));

        $plugin = new ReturnAdvancePlugin();

        $result = $plugin->assembly($rocket, function ($rocket) {return $rocket;});

        $radar = $result->getRadar();
        $payload = $result->getPayload();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_SERVICE] . 'v3/ecommerce/refunds/123/return-advance'), $radar->getUri());
        self::assertEquals('1600314070', $payload->get('sub_mchid'));
    }
}
