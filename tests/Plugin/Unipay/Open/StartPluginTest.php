<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Unipay\Open;

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;
use Yansongda\Pay\Tests\TestCase;

class StartPluginTest extends TestCase
{
    protected StartPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new StartPlugin();
    }

    public function testNormal()
    {
        $params = [
            'txnTime' => '20220903065448',
            'txnAmt' => 1,
            'orderId' => 'yansongda20220903065448',
        ];
        $payload = array_merge($params, [
            '_unpack_raw' => true,
            'certId' => '69903319369',
        ]);

        $rocket = (new Rocket())->setParams($params);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        /** @var UnipayConfig $config */
        $config = Pay::get(ConfigInterface::class)->get('unipay.default');

        self::assertEquals($payload, $result->getPayload()->all());
        self::assertArrayHasKey('cert', $config->getCerts());
        self::assertArrayHasKey('pkey', $config->getCerts());
        self::assertEquals('69903319369', $config->getCerts()['cert_id']);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertTrue(true);
    }

    public function testCertsCached()
    {
        /** @var UnipayConfig $config */
        $config = Pay::get(ConfigInterface::class)->get('unipay.default');
        $config->setCerts([]);

        $result = $this->plugin->assembly(new Rocket(), function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('69903319369', $payload->get('certId'));
        self::assertEquals('69903319369', $config->getCerts()['cert_id']);
    }
}
