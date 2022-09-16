<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\OnlineGateway;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\WapPayPlugin;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class WapPayPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Unipay\OnlineGateway\WapPayPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new WapPayPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();
        $payload = $result->getPayload();

        self::assertInstanceOf(RequestInterface::class, $radar);
        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals(new Uri(Unipay::URL[Pay::MODE_NORMAL].'gateway/api/frontTransReq.do'), $radar->getUri());
        self::assertEquals(ResponseParser::class, $result->getDirection());
        self::assertEquals('000201', $payload['bizType']);
        self::assertEquals('01', $payload['txnType']);
        self::assertEquals('01', $payload['txnSubType']);
        self::assertEquals('08', $payload['channelType']);
    }
}
