<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\Qra\Scan;

use Yansongda\Pay\Packer\XmlPacker;
use Yansongda\Pay\Plugin\Unipay\Qra\Scan\QueryPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Str;

class QueryPluginTest extends TestCase
{
    protected QueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'qra']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(XmlPacker::class, $result->getPacker());
        self::assertEquals('https://qra.95516.com/pay/gateway', $payload->get('_url'));
        self::assertEquals('unified.trade.query', $payload->get('service'));
        self::assertEquals('UTF-8', $payload->get('charset'));
        self::assertEquals('MD5', $payload->get('sign_type'));
        self::assertEquals('QRA29045311KKR1', $payload->get('mch_id'));
        self::assertEquals(32, Str::length($payload->get('nonce_str')));
    }
}
