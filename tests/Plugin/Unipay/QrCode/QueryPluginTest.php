<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\QrCode;

use Yansongda\Pay\Plugin\Unipay\QrCode\QueryPlugin;
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

        self::assertEquals([
            '_url' => 'gateway/api/backTransReq.do',
            '_sandbox_url' => 'https://101.231.204.80:5000/gateway/api/backTransReq.do',
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
            '_sandbox_url' => 'https://101.231.204.80:5000/gateway/api/backTransReq.do',
            'accessType' => '0',
            'bizType' => '000000',
            'txnType' => '00',
            'txnSubType' => '00',
        ], $payload->all());
    }
}
