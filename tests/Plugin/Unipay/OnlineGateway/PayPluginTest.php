<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\OnlineGateway;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\PayPlugin;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PayPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Unipay\OnlineGateway\PayPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();

        self::assertInstanceOf(RequestInterface::class, $radar);
        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals(new Uri(Unipay::URL[Pay::MODE_NORMAL].'gateway/api/frontTransReq.do'), $radar->getUri());
        self::assertEquals(ResponseParser::class, $result->getDirection());
    }
}
