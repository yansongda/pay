<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Common;

use Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class InvokePrepayPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = (new Rocket())->setDestination(new Collection(['prepay_id' => 'yansongda']));

        $result = (new InvokePrepayPlugin())->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('appId', $contents->all());
        self::assertArrayHasKey('package', $contents->all());
        self::assertArrayHasKey('paySign', $contents->all());
    }
}
