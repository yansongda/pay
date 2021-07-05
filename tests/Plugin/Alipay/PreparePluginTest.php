<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Alipay\PreparePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class PreparePluginTest extends TestCase
{
    protected function setUp(): void
    {
        $config = [
            'alipay' => [
                'default' => [
                    'app_public_cert_path' => __DIR__ . '/../../Cert/alipayAppCertPublicKey_2016082000295641.crt',
                    'alipay_public_cert_path' => __DIR__ . '/../../Cert/alipayCertPublicKey_RSA2.crt',
                    'alipay_root_cert_path' => __DIR__ . '/../../Cert/alipayRootCert.crt',
                ],
            ]
        ];
        Pay::config($config);
    }

    protected function tearDown(): void
    {
        Pay::clear();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $plugin = new PreparePlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertTrue($result->getPayload()->has('app_cert_sn'));
        self::assertEquals('fb5e86cfb784de936dd3594e32381cf8', $result->getPayload()->get('app_cert_sn'));
        self::assertEquals('687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6', $result->getPayload()->get('alipay_root_cert_sn'));
    }
}
