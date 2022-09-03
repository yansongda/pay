<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\Stubs\Plugin\UnipayGeneralPluginStub;
use Yansongda\Pay\Tests\Stubs\Plugin\UnipayGeneralPluginStub1;
use Yansongda\Pay\Tests\TestCase;

class GeneralPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Tests\Stubs\Plugin\UnipayGeneralPluginStub
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new UnipayGeneralPluginStub();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();

        self::assertInstanceOf(RequestInterface::class, $radar);
        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals(new Uri(Unipay::URL[Pay::MODE_NORMAL].'yansongda/pay'), $radar->getUri());
    }

    public function testAbsoluteUrl()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = (new UnipayGeneralPluginStub1())->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();

        self::assertEquals(new Uri('https://yansongda.cn/pay'), $radar->getUri());
    }
}
