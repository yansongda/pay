<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\Pay\H5;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Unipay\Pay\H5\PayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PayPluginTest extends TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
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

        self::assertEquals(ResponseDirection::class, $result->getDirection());
        self::assertEquals([
            '_url' => 'gateway/api/frontTransReq.do',
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
            'frontUrl' => 'https://pay.yansongda.cn',
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

        self::assertEquals(ResponseDirection::class, $result->getDirection());
        self::assertEquals([
            '_url' => 'gateway/api/frontTransReq.do',
            'encoding' => 'utf-8',
            'signature' => '',
            'bizType' => '000201',
            'accessType' => '0',
            'currencyCode' => '156',
            'merId' => '777290058167151',
            'channelType' => '07',
            'signMethod' => '01',
            'txnType' => '01',
            'txnSubType' => '01',
            'frontUrl' => 'https://pay.yansongda.cn',
            'backUrl' => 'https://pay.yansongda.cn',
            'version' => '5.1.0',
        ], $payload->all());
    }
}
