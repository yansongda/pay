<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\Open\Pay\QrCode;

use Yansongda\Artful\Packer\QueryPacker;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanFeePlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;

class ScanFeePluginTest extends TestCase
{
    protected ScanFeePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ScanFeePlugin();
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
            'merId' => '777290058167151',
            'currencyCode' => '156',
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
            'bizType' => '000601',
            'accessType' => '0',
            'merId' => '777290058167151',
            'currencyCode' => '156',
            'channelType' => '07',
            'signMethod' => '01',
            'txnType' => '13',
            'txnSubType' => '08',
            'backUrl' => 'https://pay.yansongda.cn',
            'version' => '5.1.0',
        ], $payload->all());
    }
}
