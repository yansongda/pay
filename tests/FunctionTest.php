<?php

namespace Yansongda\Pay\Tests;

use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Parser\CollectionParser;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class FunctionTest extends TestCase
{
    protected function tearDown (): void
    {
        Pay::clear();
    }

    public function testShouldDoHttpRequest()
    {
        $rocket = new Rocket();

        self::assertTrue(should_do_http_request($rocket));

        $rocket->setDirection(CollectionParser::class);
        self::assertTrue(should_do_http_request($rocket));

        $rocket->setDirection(ResponseParser::class);
        self::assertFalse(should_do_http_request($rocket));

        $rocket->setDirection(NoHttpRequestParser::class);
        self::assertFalse(should_do_http_request($rocket));
    }

    public function testGetAlipayConfig()
    {
        $config1 = [];
        Pay::config($config1);
        self::assertEquals([], get_alipay_config([])->all());

        Pay::clear();

        $config2 = [
            'alipay' => [
                'default' => ['name' => 'yansongda'],
                'c1' => ['age' => 28]
            ]
        ];
        Pay::config($config2);
        self::assertEquals(['name' => 'yansongda'], get_alipay_config([])->all());

        self::assertEquals(['age' => 28], get_alipay_config(['_config' => 'c1'])->all());
    }

    public function testGetPublicCrtOrPrivateCert()
    {
        $alipayPublicCertPath = __DIR__.'/Stubs/cert/alipayCertPublicKey_RSA2.crt';
        $appSecretCert = file_get_contents( __DIR__.'/Stubs/cert/appSecretKey_RSA2_PKCS1.txt');
        $appSecretCertPath = __DIR__.'/Stubs/cert/appSecretKey_RSA2_PKCS1.pem';

        self::assertEquals(file_get_contents($alipayPublicCertPath), get_public_crt_or_private_cert($alipayPublicCertPath));
        self::assertTrue(Str::contains(get_public_crt_or_private_cert($appSecretCert), 'END RSA PRIVATE KEY'));

        // github action 不支持 pem
        // self::assertIsResource(get_public_crt_or_private_cert($appSecretCertPath));
    }

    public function testVerifyAlipayResponse()
    {
        $config1 = [
            'alipay' => [
                'default' => [
                    // 'alipay_public_cert_path' => __DIR__.'/Stubs/cert/alipayCertPublicKey_RSA2.crt'
                ],
            ]
        ];
        Pay::config($config1);

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(InvalidConfigException::ALIPAY_CONFIG_ERROR);
        verify_alipay_response([], '', '');
    }
}
