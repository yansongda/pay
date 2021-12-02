<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Tools;

use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Alipay\Tools\SystemOauthTokenPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class SystemOauthTokenPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['name' => 'yansongda', 'age' => 28]);

        $plugin = new SystemOauthTokenPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('yansongda', $result->getPayload()->get('name'));
        self::assertEquals(28, $result->getPayload()->get('age'));
    }
}
