<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay;

use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Unipay\PreparePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PreparePluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Unipay\PreparePlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PreparePlugin();
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
            'bizType' => '000201',
            'backUrl' => 'https://yansongda.cn/unipay/notify',
            'currencyCode' => '156',
            'txnType' => '01',
            'txnSubType' => '01',
            'accessType' => '0',
            'signature' => '',
            'signMethod' => '01',
            'channelType' => '07',
            'merId' => '777290058167151',
            'frontUrl' => 'https://yansongda.cn/unipay/return',
            'certId' => '69903319369',
        ]);

        $rocket = (new Rocket())->setParams($params);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $config = get_unipay_config([]);

        self::assertEquals($payload, $result->getPayload()->all());
        self::assertArrayHasKey('cert', $config['certs']);
        self::assertArrayHasKey('pkey', $config['certs']);
        self::assertEquals('69903319369', $config['certs']['cert_id']);

        Pay::set(ConfigInterface::class, Pay::get(ConfigInterface::class)->merge([
            'unipay' => ['default' => array_merge($config, ['mch_cert_path' => null])],
        ]));

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertTrue(true);
    }
}
