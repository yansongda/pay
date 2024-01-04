<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\Pay\QrCode;

use Yansongda\Pay\Plugin\Unipay\Pay\QrCode\ScanPreOrderPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class ScanPreOrderPluginTest extends TestCase
{
    protected ScanPreOrderPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ScanPreOrderPlugin();
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

        self::assertEquals([
            '_url' => 'gateway/api/order.do',
            'accessType' => '1',
            'bizType' => '2',
            'txnType' => '3',
            'txnSubType' => '4',
            'channelType' => '5',
        ], $payload->all());
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals([
            '_url' => 'gateway/api/order.do',
            'accessType' => '0',
            'bizType' => '000000',
            'txnType' => '01',
            'txnSubType' => '01',
            'channelType' => '08',
        ], $payload->all());
    }
}
