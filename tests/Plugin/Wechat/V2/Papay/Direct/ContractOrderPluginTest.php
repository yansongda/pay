<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V2\Papay\Direct;

use Yansongda\Pay\Packer\XmlPacker;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\ContractOrderPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ContractOrderPluginTest extends TestCase
{
    protected ContractOrderPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ContractOrderPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "out_trade_no" => "111",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals(XmlPacker::class, $result->getPacker());
        self::assertEquals('pay/contractorder', $payload->get('_url'));
        self::assertEquals('application/xml', $payload->get('_content_type'));
        self::assertEquals('111', $payload->get('out_trade_no'));
        self::assertEquals('wx55955316af4ef13', $payload->get('appid'));
        self::assertEquals('1600314069', $payload->get('mch_id'));
        self::assertEquals('wx55955316af4ef13', $payload->get('contract_appid'));
        self::assertEquals('1600314069', $payload->get('contract_mchid'));
        self::assertEquals('MD5', $payload->get('sign_type'));
        self::assertNotEmpty($payload->get('nonce_str'));
        self::assertArrayHasKey('notify_url', $payload->all());
        self::assertArrayHasKey('contract_notify_url', $payload->all());
    }
}
