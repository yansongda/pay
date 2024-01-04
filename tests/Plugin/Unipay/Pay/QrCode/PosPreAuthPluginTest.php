<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\Pay\QrCode;

use Yansongda\Pay\Plugin\Unipay\Pay\QrCode\PosPreAuthPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PosPreAuthPluginTest extends TestCase
{
    protected PosPreAuthPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PosPreAuthPlugin();
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
            '_url' => 'gateway/api/backTransReq.do',
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
            '_url' => 'gateway/api/backTransReq.do',
            'accessType' => '0',
            'bizType' => '000201',
            'txnType' => '02',
            'txnSubType' => '04',
            'channelType' => '08',
        ], $payload->all());
    }
}
