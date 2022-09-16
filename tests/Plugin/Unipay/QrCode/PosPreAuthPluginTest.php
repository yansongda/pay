<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\QrCode;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Unipay\QrCode\PosPreAuthPlugin;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PosPreAuthPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Unipay\QrCode\PosPreAuthPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PosPreAuthPlugin();
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
        self::assertEquals(new Uri(Unipay::URL[Pay::MODE_NORMAL].'gateway/api/backTransReq.do'), $radar->getUri());
        self::assertEquals('000201', $payload['bizType']);
        self::assertEquals('02', $payload['txnType']);
        self::assertEquals('04', $payload['txnSubType']);
        self::assertEquals('08', $payload['channelType']);
    }
}
