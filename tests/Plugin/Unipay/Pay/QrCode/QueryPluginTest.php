<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\Pay\QrCode;

use Yansongda\Pay\Packer\QueryPacker;
use Yansongda\Pay\Plugin\Unipay\Pay\QrCode\QueryPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class QueryPluginTest extends TestCase
{
    protected QueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryPlugin();
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
            '_sandbox_url' => 'https://101.231.204.80:5000/gateway/api/backTransReq.do',
            'encoding' => 'utf-8',
            'signature' => '',
            'bizType' => '2',
            'accessType' => '1',
            'merId' => '777290058167151',
            'channelType' => '5',
            'signMethod' => '01',
            'txnType' => '3',
            'txnSubType' => '4',
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
            '_sandbox_url' => 'https://101.231.204.80:5000/gateway/api/backTransReq.do',
            'encoding' => 'utf-8',
            'signature' => '',
            'bizType' => '000000',
            'accessType' => '0',
            'merId' => '777290058167151',
            'signMethod' => '01',
            'txnType' => '00',
            'txnSubType' => '00',
            'version' => '5.1.0',
        ], $payload->all());
    }
}
