<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Ecommerce;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund\FindRefundPlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class FindRefundPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection());

        $plugin = new FindRefundPlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::SERVICE_NOT_FOUND_ERROR);

        $plugin->assembly($rocket, function ($rocket) {return $rocket; });
    }

    public function testPartner()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection());

        $plugin = new FindRefundPlugin();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) {return $rocket; });
    }

    public function testPartnerRefundIdDirectPayload()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection(['refund_id' => '123']));

        $plugin = new FindRefundPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

        $radar = $result->getRadar();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_SERVICE].'v3/ecommerce/refunds/id/123?sub_mchid=1600314070'), $radar->getUri());
        self::assertEquals('GET', $radar->getMethod());
    }

    public function testPartnerOutRefundNoDirectPayload()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection(['out_refund_no' => '123']));

        $plugin = new FindRefundPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

        $radar = $result->getRadar();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_SERVICE].'v3/ecommerce/refunds/out-refund-no/123?sub_mchid=1600314070'), $radar->getUri());
        self::assertEquals('GET', $radar->getMethod());
    }
}
