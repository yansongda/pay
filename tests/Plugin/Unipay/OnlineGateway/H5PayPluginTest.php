<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\OnlineGateway;

use Yansongda\Pay\Plugin\Unipay\OnlineGateway\H5PayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class H5PayPluginTest extends TestCase
{
    protected H5PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new H5PayPlugin();
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
            '_url' => 'gateway/api/frontTransReq.do',
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
            '_url' => 'gateway/api/frontTransReq.do',
            'accessType' => '0',
            'bizType' => '000201',
            'txnType' => '01',
            'txnSubType' => '01',
            'channelType' => '08',
        ], $payload->all());
    }
}
