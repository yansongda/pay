<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\Open\Pay\Web;

use Yansongda\Pay\Packer\QueryPacker;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\Web\RefundPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class RefundPluginTest extends TestCase
{
    protected RefundPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundPlugin();
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
            'merId' => '777290058167151',
            'channelType' => '07',
            'signMethod' => '01',
            'txnType' => '04',
            'txnSubType' => '00',
            'backUrl' => 'https://pay.yansongda.cn',
            'version' => '5.1.0',
        ], $payload->all());
    }
}
