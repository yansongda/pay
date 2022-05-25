<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Fund\Transfer;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Fund\Transfer\CreateDetailReceiptPlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CreateDetailReceiptPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Wechat\Fund\Transfer\CreateDetailReceiptPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CreateDetailReceiptPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['out_detail_no' => '123', 'accept_type' => '456']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();
        $payload = $result->getPayload();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/transfer-detail/electronic-receipts'), $radar->getUri());
        self::assertEquals('123', $payload->get('out_detail_no'));
        self::assertEquals('456', $payload->get('accept_type'));
    }

    public function testNormalNoOutBatchNo()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['accept_type' => '456']));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalNoAcceptType()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['out_detail_no' => '123']));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testPartner()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection(['out_detail_no' => '123', 'accept_type' => '456']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();
        $payload = $result->getPayload();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/transfer-detail/electronic-receipts'), $radar->getUri());
        self::assertEquals('123', $payload->get('out_detail_no'));
        self::assertEquals('456', $payload->get('accept_type'));
    }
}
