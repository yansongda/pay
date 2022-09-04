<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Plugin\Wechat\Pay\Common\QueryPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;
use function Yansongda\Pay\get_wechat_config;

class QueryPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Wechat\Pay\Common\QueryPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryPlugin();
    }

    public function testNormalTransactionId()
    {
        $rocket = new Rocket();
        $config = get_wechat_config($rocket->getParams([]));

        $rocket->setPayload(new Collection(['transaction_id'=>'121212']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('1600314069', $config['mch_id']);
        self::assertEquals('/v3/pay/transactions/id/121212', $radar->getUri()->getPath());
        self::assertEquals('mchid=1600314069', $radar->getUri()->getQuery());
        self::assertEquals('GET', $radar->getMethod());
        self::assertStringNotContainsString('sp_mchid', $radar->getUri()->getQuery());
    }

    public function testNormalOutTradeNo()
    {
        $rocket = new Rocket();

        $rocket->setPayload(new Collection(['out_trade_no'=>'121212']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('/v3/pay/transactions/out-trade-no/121212', $radar->getUri()->getPath());
        self::assertEquals('mchid=1600314069', $radar->getUri()->getQuery());
        self::assertEquals('GET', $radar->getMethod());
        self::assertStringNotContainsString('sp_mchid', $radar->getUri()->getQuery());
    }

    public function testPartnerTransactionId()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider']);
        $rocket->setPayload(new Collection(['transaction_id'=>'121212','sub_mchid' => '1600314077']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('/v3/pay/partner/transactions/id/121212', $radar->getUri()->getPath());
        self::assertEquals('GET', $radar->getMethod());
        self::assertStringContainsString('sub_mchid=1600314077', $radar->getUri()->getQuery());
        self::assertStringContainsString('sp_mchid=1600314069', $radar->getUri()->getQuery());
    }

    public function testPartnerOutTradeNo()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider']);
        $rocket->setPayload(new Collection(['out_trade_no'=>'121218','sub_mchid' => '1600314099']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('/v3/pay/partner/transactions/out-trade-no/121218', $radar->getUri()->getPath());
        self::assertEquals('GET', $radar->getMethod());
        self::assertStringContainsString('sub_mchid=1600314099', $radar->getUri()->getQuery());
        self::assertStringContainsString('sp_mchid=1600314069', $radar->getUri()->getQuery());
    }
}
