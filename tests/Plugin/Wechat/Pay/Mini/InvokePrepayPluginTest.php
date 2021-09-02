<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\Mini;

use Yansongda\Pay\Plugin\Wechat\Pay\Mini\InvokePrepayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class InvokePrepayPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = (new Rocket())->setDestination(new Collection(['prepay_id' => 'yansongda anthony']));

        $result = (new InvokePrepayPlugin())->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('appId', $contents->all());
        self::assertEquals('wx55955316af4ef14', $contents->get('appId'));
        self::assertArrayHasKey('nonceStr', $contents->all());
        self::assertArrayHasKey('package', $contents->all());
        self::assertArrayHasKey('signType', $contents->all());
        self::assertArrayHasKey('paySign', $contents->all());
    }

    public function testPartner()
    {
        $rocket = (new Rocket())->setParams(['_config' => 'service_provider']);
        $rocket->setPayload(new Collection(['out_trade_no'=>'121218','sub_appid' =>'wx55955316af4ef88']));
        $rocket->setDestination(new Collection(['prepay_id' => 'yansongda anthony']));

        $result = (new InvokePrepayPlugin())->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('appId', $contents->all());
        self::assertEquals('wx55955316af4ef88', $contents->get('appId'));
        self::assertArrayHasKey('nonceStr', $contents->all());
        self::assertArrayHasKey('package', $contents->all());
        self::assertArrayHasKey('signType', $contents->all());
        self::assertArrayHasKey('paySign', $contents->all());
    }
}
