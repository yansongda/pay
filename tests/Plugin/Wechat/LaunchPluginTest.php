<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat;

use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Plugin\Wechat\LaunchPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class LaunchPluginTest extends TestCase
{
    public function testShouldNotDoRequest()
    {
        $rocket = new Rocket();
        $rocket->setDirection(NoHttpRequestParser::class);

        $plugin = new LaunchPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
    }
}
