<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay;

use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Unipay\StartPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use function Yansongda\Pay\get_unipay_config;

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
            'certId' => '69903319369',
        ]);

        $rocket = (new Rocket())->setParams($params);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $config = get_unipay_config([]);

        self::assertEquals($payload, $result->getPayload()->all());
        self::assertArrayHasKey('cert', $config['certs']);
        self::assertArrayHasKey('pkey', $config['certs']);
        self::assertEquals('69903319369', $config['certs']['cert_id']);

        Pay::get(ConfigInterface::class)->set('unipay.default.mch_cert_path', null);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertTrue(true);
    }

    public function testCertsCached()
    {
        $config = Pay::get(ConfigInterface::class);
        $config->set('unipay.default.certs', null);

        $result = $this->plugin->assembly(new Rocket(), function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('69903319369', $payload->get('certId'));
        self::assertEquals('69903319369', $config->get('unipay.default.certs.cert_id'));
    }
}
