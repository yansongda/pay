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
            'version' => '5.1.0',
            'encoding' => 'utf-8',
            'backUrl' => 'https://pay.yansongda.cn',
            'accessType' => '0',
            'signature' => '',
            'signMethod' => '01',
            'merId' => '777290058167151',
            'frontUrl' => 'https://pay.yansongda.cn',
            'certId' => '69903319369',
            'currencyCode' => '156',
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

    public function testCustomizedNotifyUrl()
    {
        $rocket = new Rocket();
        $rocket->setParams([
            '_notify_url' => 'https://yansongda.cna',
            '_return_url' => 'https://yansongda.cnaa',
        ]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('https://yansongda.cna', $result->getPayload()->get('backUrl'));
        self::assertEquals('https://yansongda.cnaa', $result->getPayload()->get('frontUrl'));
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
