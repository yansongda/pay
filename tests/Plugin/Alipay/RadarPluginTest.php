<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Alipay\PreparePlugin;
use Yansongda\Pay\Plugin\Alipay\RadarPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class RadarPluginTest extends TestCase
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

    public function testPostNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['name' => 'yansongda']));

        $plugin = new RadarPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('name=yansongda', $result->getRadar()->getBody()->getContents());
        self::assertEquals('POST', $result->getRadar()->getMethod());
    }

    public function testGetNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_method' => 'get'])->setPayload(new Collection(['name' => 'yansongda']));

        $plugin = new RadarPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('GET', $result->getRadar()->getMethod());
    }
}
