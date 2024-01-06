<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\Open\Pay\QrCode;

use Yansongda\Pay\Packer\QueryPacker;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\PosPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PosPluginTest extends TestCase
{
    protected PosPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PosPlugin();
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload([
            'accessType' => '1',
            'bizType' => '2',
            'txnType' => '3',
            'txnSubType' => '4',
            'channelType' => '5',
        ]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(QueryPacker::class, $result->getPacker());
        self::assertEquals([
            '_url' => 'gateway/api/backTransReq.do',
            'encoding' => 'utf-8',
            'signature' => '',
            'bizType' => '2',
            'accessType' => '1',
            'currencyCode' => '156',
            'merId' => '777290058167151',
            'channelType' => '5',
            'signMethod' => '01',
            'txnType' => '3',
            'txnSubType' => '4',
            'backUrl' => 'https://pay.yansongda.cn',
            'version' => '5.1.0',
        ], $payload->all());
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(QueryPacker::class, $result->getPacker());
        self::assertEquals([
            '_url' => 'gateway/api/backTransReq.do',
            'encoding' => 'utf-8',
            'signature' => '',
            'bizType' => '000000',
            'accessType' => '0',
            'currencyCode' => '156',
            'merId' => '777290058167151',
            'channelType' => '08',
            'signMethod' => '01',
            'txnType' => '01',
            'txnSubType' => '06',
            'backUrl' => 'https://pay.yansongda.cn',
            'version' => '5.1.0',
        ], $payload->all());
    }
}
