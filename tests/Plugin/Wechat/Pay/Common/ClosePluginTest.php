<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\Common;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Pay\Common\ClosePlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ClosePluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Wechat\Pay\Common\ClosePlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ClosePlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['out_trade_no' => '123']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();
        $payload = $result->getPayload();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/pay/transactions/out-trade-no/123/close'), $radar->getUri());
        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('1600314069', $payload->get('mchid'));
        self::assertArrayNotHasKey('sp_mchid', $payload->all());
        self::assertArrayNotHasKey('sub_mchid', $payload->all());
    }

    public function testNormalNoOutTradeNo()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection());

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testPartner()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection(['out_trade_no' => '123']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();
        $payload = $result->getPayload();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/pay/partner/transactions/out-trade-no/123/close'), $radar->getUri());
        self::assertEquals('1600314069', $payload->get('sp_mchid'));
        self::assertEquals('1600314070', $payload->get('sub_mchid'));
        self::assertArrayNotHasKey('mchid', $payload->all());
    }

    public function testPartnerDirectPayload()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection(['out_trade_no' => '123', 'sub_mchid' => '123']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('123', $payload->get('sub_mchid'));
    }
}
