<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Risk\Complaints;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Parser\OriginResponseParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Risk\Complaints\DeleteCallbackPlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class DeleteCallbackPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Wechat\Risk\Complaints\DeleteCallbackPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new DeleteCallbackPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['complaint_id' => '123', 'foo' => 'bar']));

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket;});

        $radar = $result->getRadar();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/merchant-service/complaint-notifications'), $radar->getUri());
        self::assertNull($rocket->getPayload());
        self::assertEquals('DELETE', $radar->getMethod());
        self::assertEquals(OriginResponseParser::class, $rocket->getDirection());
    }
}
