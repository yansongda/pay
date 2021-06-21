<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;
use Yansongda\Pay\Rocket;

class GeneralPayPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $plugin = new FooPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertStringContainsString('yansongda', $result->getPayload()->toJson());
    }
}

class FooPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'yansongda';
    }
}
