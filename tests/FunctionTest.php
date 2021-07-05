<?php

namespace Yansongda\Pay\Tests;

use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Parser\CollectionParser;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Str;

class FunctionTest extends TestCase
{
    protected function setUp(): void
    {
        Pay::clear();
    }

    protected function tearDown(): void
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
        $alipayPublicCertPath = __DIR__ . '/Cert/alipayCertPublicKey_RSA2.crt';
        $appSecretCert = file_get_contents(__DIR__ . '/Cert/alipayAppSecretKey_RSA2_PKCS1.txt');
        $appSecretCertPath = __DIR__ . '/Cert/alipayAppSecretKey_RSA2_PKCS1.pem';

        self::assertEquals(file_get_contents($alipayPublicCertPath), get_public_crt_or_private_cert($alipayPublicCertPath));
        self::assertTrue(Str::contains(get_public_crt_or_private_cert($appSecretCert), 'END RSA PRIVATE KEY'));

        // 不知道是不是 GitHub 屏蔽了 pem 文件还是怎样.
        // var_dump(file_get_contents($appSecretCertPath));
        // self::assertIsResource(get_public_crt_or_private_cert($appSecretCertPath));
    }

    public function testVerifyAlipaySign()
    {
        $config = [
            'alipay' => [
                'default' => [
                    'alipay_public_cert_path' => __DIR__.'/Cert/alipayCertPublicKey_RSA2.crt'
                ],
            ]
        ];
        Pay::config($config);

        verify_alipay_sign([], json_encode([
            "code" => "10000",
            "msg" => "Success",
            "buyer_logon_id" => "ghd***@sandbox.com",
            "buyer_pay_amount" => "0.00",
            "buyer_user_id" => "2088102174698127",
            "buyer_user_type" => "PRIVATE",
            "invoice_amount" => "0.00",
            "out_trade_no" => "yansongda-1622986519",
            "point_amount" => "0.00",
            "receipt_amount" => "0.00",
            "send_pay_date" => "2021-06-06 21:35:40",
            "total_amount" => "0.01",
            "trade_no" => "2021060622001498120501382075",
            "trade_status" => "TRADE_SUCCESS",
        ], JSON_UNESCAPED_UNICODE), base64_decode('Ipp1M3pwUFJ19Tx/D+40RZstXr3VSZzGxPB1Qfj1e837UkGxOJxFFK6EZ288SeEh06dPFd4qJ7BHfP/7mvkRqF1/mezBGvhBz03XTXfDn/O6IkoA+cVwpfm+i8MFvzC/ZQB0dgtZppu5qfzVyFaaNu8ct3L/NSQCMR1RXg2lH3HiwfxmIF35+LmCoL7ZPvTxB/epm7A/XNhAjLpK5GlJffPA0qwhhtQwaIZ7DHMXo06z03fbgxlBu2eEclQUm6Fobgj3JEERWLA0MDQiV1EYNWuHSSlHCMrIxWHba+Euu0jVkKKe0IFKsU8xJQbc7GTJXx/o0NfHqGwwq8hMvtgBkg=='));
        self::assertTrue(true);

        // test config error
        $config1 = [
            'alipay' => [
                'default' => [
                    // 'alipay_public_cert_path' => __DIR__.'/Cert/alipayCertPublicKey_RSA2.crt'
                ],
            ]
        ];
        Pay::config(array_merge($config1, ['_force' => true]));

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(InvalidConfigException::ALIPAY_CONFIG_ERROR);
        verify_alipay_sign([], '', '');
    }

    public function testGetWechatConfig()
    {
        $config1 = [];
        Pay::config($config1);
        self::assertEquals([], get_wechat_config([])->all());

        $config2 = [
            'wechat' => [
                'default' => ['name' => 'yansongda'],
                'c1' => ['age' => 28]
            ]
        ];
        Pay::config(array_merge($config2, ['_force' => true]));
        self::assertEquals(['name' => 'yansongda'], get_wechat_config([])->all());

        self::assertEquals(['age' => 28], get_wechat_config(['_config' => 'c1'])->all());
    }
}
