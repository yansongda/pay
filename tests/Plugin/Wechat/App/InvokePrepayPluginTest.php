<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\App;

use Yansongda\Pay\Plugin\Wechat\Pay\App\InvokePrepayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class InvokePrepayPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = (new Rocket())->setDestination(new Collection(['prepay_id' => 'yansongda']));

        $result = (new InvokePrepayPlugin())->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination()->getBody()->getContents();

        self::assertStringContainsString('appid', $contents);
        self::assertStringContainsString('partnerid', $contents);
        self::assertStringContainsString('package', $contents);
        self::assertStringContainsString('Sign=WXPay', $contents);
        self::assertStringContainsString('sign', $contents);
    }
}
