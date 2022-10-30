<?php

namespace Yansongda\Pay\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Parser\CollectionParser;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Str;
use function Yansongda\Pay\decrypt_wechat_resource;
use function Yansongda\Pay\decrypt_wechat_resource_aes_256_gcm;
use function Yansongda\Pay\encrypt_wechat_contents;
use function Yansongda\Pay\get_alipay_config;
use function Yansongda\Pay\get_private_cert;
use function Yansongda\Pay\get_public_cert;
use function Yansongda\Pay\get_tenant;
use function Yansongda\Pay\get_unipay_config;
use function Yansongda\Pay\get_wechat_base_uri;
use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_sign;
use function Yansongda\Pay\reload_wechat_public_certs;
use function Yansongda\Pay\should_do_http_request;
use function Yansongda\Pay\verify_alipay_sign;
use function Yansongda\Pay\verify_unipay_sign;
use function Yansongda\Pay\verify_wechat_sign;

class FunctionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testShouldDoHttpRequest()
    {
        $rocket = new Rocket();

        self::assertTrue(should_do_http_request($rocket->getDirection()));

        $rocket->setDirection(CollectionParser::class);
        self::assertTrue(should_do_http_request($rocket->getDirection()));

        $rocket->setDirection(ResponseParser::class);
        self::assertFalse(should_do_http_request($rocket->getDirection()));

        $rocket->setDirection(NoHttpRequestParser::class);
        self::assertFalse(should_do_http_request($rocket->getDirection()));
    }

    public function testGetTenant()
    {
        self::assertEquals('default', get_tenant([]));
        self::assertEquals('yansongda', get_tenant(['_config' => 'yansongda']));
    }

    public function testGetAlipayConfig()
    {
        self::assertArrayHasKey('app_id', get_alipay_config([]));

        Pay::clear();

        $config2 = [
            'alipay' => [
                'default' => ['name' => 'yansongda'],
                'c1' => ['age' => 28]
            ]
        ];
        Pay::config($config2);
        self::assertEquals(['name' => 'yansongda'], get_alipay_config([]));

        self::assertEquals(['age' => 28], get_alipay_config(['_config' => 'c1']));
    }

    public function testGetPublicCert()
    {
        $alipayPublicCertPath = __DIR__ . '/Cert/alipayCertPublicKey_RSA2.crt';
        $alipayPublicCertCerPath = __DIR__ . '/Cert/alipayCertPublicKey_RSA2.cer';

        self::assertEquals(file_get_contents($alipayPublicCertCerPath), get_public_cert($alipayPublicCertCerPath));
        self::assertEquals(file_get_contents($alipayPublicCertPath), get_public_cert($alipayPublicCertPath));
    }

    public function testGetPrivateCert()
    {
        $appSecretCert = file_get_contents(__DIR__ . '/Cert/alipayAppSecretKey_RSA2_PKCS1.txt');

        self::assertTrue(Str::contains(get_private_cert($appSecretCert), 'PRIVATE KEY'));
    }

    public function testVerifyAlipaySign()
    {
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
        ], JSON_UNESCAPED_UNICODE), 'Ipp1M3pwUFJ19Tx/D+40RZstXr3VSZzGxPB1Qfj1e837UkGxOJxFFK6EZ288SeEh06dPFd4qJ7BHfP/7mvkRqF1/mezBGvhBz03XTXfDn/O6IkoA+cVwpfm+i8MFvzC/ZQB0dgtZppu5qfzVyFaaNu8ct3L/NSQCMR1RXg2lH3HiwfxmIF35+LmCoL7ZPvTxB/epm7A/XNhAjLpK5GlJffPA0qwhhtQwaIZ7DHMXo06z03fbgxlBu2eEclQUm6Fobgj3JEERWLA0MDQiV1EYNWuHSSlHCMrIxWHba+Euu0jVkKKe0IFKsU8xJQbc7GTJXx/o0NfHqGwwq8hMvtgBkg==');
        self::assertTrue(true);

        // test config error
        $config1 = [
            'alipay' => [
                'default' => [
                    'alipay_public_cert_path' => ''
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
        self::assertArrayHasKey('mp_app_id', get_wechat_config([]));

        $config2 = [
            'wechat' => [
                'default' => ['name' => 'yansongda'],
                'c1' => ['age' => 28]
            ]
        ];
        Pay::config(array_merge($config2, ['_force' => true]));
        self::assertEquals(['name' => 'yansongda'], get_wechat_config([]));

        self::assertEquals(['age' => 28], get_wechat_config(['_config' => 'c1']));
    }

    public function testGetWechatBaseUri()
    {
        self::assertEquals(Wechat::URL[Pay::MODE_NORMAL], get_wechat_base_uri([]));

        $config2 = ['_force' => true, 'wechat' => [
            'yansongda' => ['mode' => Pay::MODE_SANDBOX]
        ]];
        Pay::config($config2);

        self::assertEquals(Wechat::URL[Pay::MODE_SANDBOX], get_wechat_base_uri(['_config' => 'yansongda']));
    }

    public function testGetWechatSign()
    {
        $params = [
            'out_trade_no' => 1626493236,
            'description' => 'yansongda 测试 - 1626493236',
            'amount' => [
                'total' => 1,
            ],
            'scene_info' => [
                'payer_client_ip' => '127.0.0.1',
                'h5_info' => [
                    'type' => 'Wap',
                ]
            ]];
        $contents = "POST\n/v3/pay/transactions/h5\n1626493236\nQqtzdVzxavZeXag9G5mtfzbfzFMf89p6\n{\"out_trade_no\":1626493236,\"description\":\"yansongda 测试 - 1626493236\",\"amount\":{\"total\":1},\"scene_info\":{\"payer_client_ip\":\"127.0.0.1\",\"h5_info\":{\"type\":\"Wap\"}},\"appid\":\"wx55955316af4ef13\",\"mchid\":\"1600314069\",\"notify_url\":\"http:\/\/127.0.0.1:8000\/wechat\/notify\"}\n";

        self::assertEquals(
            'KzIgMgiop3nQJNdBVR2Xah/JUwVBLDFFajyXPiSN8b8YAYEA4FuWfaCgFJ52+WFed+PhOYWx/ZPih4RaEuuSdYB8eZwYUx7RZGMQZk0bKCctAjjPuf4pJN+f/WsXKjPIy3diqF5x7gyxwSCaKWP4/KjsHNqgQpiC8q1uC5xmElzuhzSwj88LIoLtkAuSmtUVvdAt0Nz41ECHZgHWSGR32TfBo902r8afdaVKkFde8IoqcEJJcp6sMxdDO5l9R5KEWxrJ1SjsXVrb0IPH8Nj7e6hfhq7pucxojPpzsC+ZWAYvufZkAQx3kTiFmY87T+QhkP9FesOfWvkIRL4E6MP6ug==',
            get_wechat_sign($params, $contents)
        );

        // test config error
        $config1 = [
            'wechat' => [
                'default' => [
                    'mch_secret_cert' => ''
                ],
            ]
        ];
        Pay::config(array_merge($config1, ['_force' => true]));

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::WECHAT_CONFIG_ERROR);
        get_wechat_sign([], '', '');
    }

    public function testVerifyWechatSign()
    {
        $response = new Response(
            200,
            [
                'Wechatpay-Nonce' => 'e59e78a6c3f7dfd7e84aabee71be0452',
                'Wechatpay-Signature' => 'Bb10ZUsON47E/qLjecjk6ESLt7obZCvCCAXAEoD1Q+K548fz9h6YBgR3PZzviTmjsA3/r22qEC3r/yelFAn4pl4rJBGqrjo4ODJkOPlaDnHZwYotDvf6RcASpKB9ExCb33hAijHCiMzr9V9skNrj5F9eXc96lNZN3R5MVLsTF97nV922JIzyCrZ668khYPrn1jl5pCBpYDQ3rskgmZ+nnjg7M9vRAfTowEydSEGtsKjXUSaaKui2RDUuX8ZwxVcBTRng978Gh9s4mdRxs+mlv3gP1xQHdpa0mYMG0yGzLcWOTgrkt27sAwFnuXj9WtlEAgz/1DYntujKPxilMVGRow==',
                'Wechatpay-Timestamp' => '1626444144',
                'Wechatpay-Serial' => '45F59D4DABF31918AFCEC556D5D2C6E376675D57',
            ],
            json_encode(['h5_url' => 'https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx16220223998099f898c5b24eed5c320000&package=4049184564'], JSON_UNESCAPED_SLASHES),
        );
        verify_wechat_sign($response, []);
        self::assertTrue(true);

        $serverRequest = new ServerRequest('POST', 'http://localhost');
        verify_wechat_sign($serverRequest, []);
        self::assertTrue(true);

        $response = new Response(
            200,
            [
                'Wechatpay-Nonce' => 'e59e78a6c3f7dfd7e84aabee71be0452',
                'Wechatpay-Signature' => 'Bb10ZUsON47E/qLjecjk6ESLt7obZCvCCAXAEoD1Q+K548fz9h6YBgR3PZzviTmjsA3/r22qEC3r/yelFAn4pl4rJBGqrjo4ODJkOPlaDnHZwYotDvf6RcASpKB9ExCb33hAijHCiMzr9V9skNrj5F9eXc96lNZN3R5MVLsTF97nV922JIzyCrZ668khYPrn1jl5pCBpYDQ3rskgmZ+nnjg7M9vRAfTowEydSEGtsKjXUSaaKui2RDUuX8ZwxVcBTRng978Gh9s4mdRxs+mlv3gP1xQHdpa0mYMG0yGzLcWOTgrkt27sAwFnuXj9WtlEAgz/1DYntujKPxilMVGRow==',
                'Wechatpay-Timestamp' => '1626444144',
                'Wechatpay-Serial' => '45F59D4DABF31918AFCEC556D5D2C6E376675D57',
            ],
            json_encode(['h5_url' => 'https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx16220223998099f898c5b24eed5c320000&package=4049184564'], JSON_UNESCAPED_SLASHES),
        );
        $response->getBody()->read(10);
        verify_wechat_sign($response, []);
        self::assertTrue(true);
    }

    public function testEncryptWechatContents()
    {
        $serialNo = '45F59D4DABF31918AFCEC556D5D2C6E376675D57';
        $contents = 'yansongda';
        $result = encrypt_wechat_contents($contents, get_wechat_config([])['wechat_public_cert_path'][$serialNo]);
        self::assertIsString($result);
    }

    public function testReloadWechatPublicCerts()
    {
        $response = new Response(
            200,
            [],
            json_encode([
                'data' => [
                    [
                        'effective_time' => '2021-07-16T17:51:10+08:00',
                        'encrypt_certificate' => [
                            'algorithm' => 'AEAD_AES_256_GCM',
                            'associated_data' => 'certificate',
                            'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
                            'nonce' => '4196a5b75276',
                        ],
                        'expire_time' => '2026-07-15T17:51:10+08:00',
                        'serial_no' => 'test-45F59D4DABF31918AFCEC556D5D2C6E376675D57',
                    ]
                ]
            ])
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        $result = reload_wechat_public_certs([], 'test-45F59D4DABF31918AFCEC556D5D2C6E376675D57');

        self::assertTrue(false !== strpos($result, '-----BEGIN CERTIFICATE-----'));
        self::assertTrue(Pay::get(ConfigInterface::class)->has('wechat.default.wechat_public_cert_path.test-45F59D4DABF31918AFCEC556D5D2C6E376675D57'));
        self::assertIsArray(Pay::get(ConfigInterface::class)->get('wechat.default'));
    }

    public function testDecryptWechatResource()
    {
        $resource = [
            'algorithm' => 'AEAD_AES_256_GCM',
            'associated_data' => 'certificate',
            'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
            'nonce' => '4196a5b75276',
        ];

        $result = decrypt_wechat_resource($resource, []);

        self::assertTrue(false !== strpos($result['ciphertext'], '-----BEGIN CERTIFICATE-----'));
    }

    public function testDecryptWechatResourceAes256Gcm()
    {
        $resource = [
            'algorithm' => 'AEAD_AES_256_GCM',
            'associated_data' => 'certificate',
            'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
            'nonce' => '4196a5b75276',
        ];
        $result = decrypt_wechat_resource_aes_256_gcm(base64_decode($resource['ciphertext']), '53D67FCB97E68F9998CBD17ED7A8D1E2', $resource['nonce'], $resource['associated_data']);
        self::assertTrue(false !== strpos($result, '-----BEGIN CERTIFICATE-----'));

        $resource = [
            'algorithm' => 'AEAD_AES_256_GCM',
            'associated_data' => 'certificatea',
            'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
            'nonce' => '4196a5b75276',
        ];
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::INVALID_REQUEST_ENCRYPTED_DATA);
        decrypt_wechat_resource_aes_256_gcm(base64_decode($resource['ciphertext']), 'foo', $resource['nonce'], $resource['associated_data']);
    }

    public function testGetUnipayConfig()
    {
        self::assertArrayHasKey('mch_id', get_unipay_config([]));

        Pay::clear();

        $config2 = [
            'unipay' => [
                'default' => ['name' => 'yansongda'],
                'c1' => ['age' => 28]
            ]
        ];
        Pay::config($config2);
        self::assertEquals(['name' => 'yansongda'], get_unipay_config([]));

        self::assertEquals(['age' => 28], get_unipay_config(['_config' => 'c1']));
    }

    public function testVerifyUnipaySign()
    {
        $contents = "accNo=ORRSXWY1kMr8UJNxGx9xKPuO0Uhm8JT8aQV3sWswJfIsj/grkjauH4soyAtiqB9XwQotZOwmUAs/pkMupUkfiX9npdFGGEUEc5gqq+lcEwyD7tLmd2WBzRvcEvvjAKMKwTCFDxmQbIrP48ocIVhPoZ87ZQtQM5MIyJYedrzPRlt6BzRddUPGU1gJwDA8APDx3iyNl8EAfenJw7DUDZimmhbE1VSRmQm/iqgJurI7juq/6ztDHZHv4ys1eN9JYkwhcKxCjsWpwXTSy0PGvDXhsAZsDuNXHsjI8JLhHXvTDaU2+gc289LZPiwpr4Ah/reIuPWrIHubchYm2XTqQlUAaw==&accessType=0&bizType=000201&currencyCode=156&encoding=utf-8&exchangeRate=0&merId=777290058167151&orderId=yansongda20220908132206&queryId=782209081322060674028&respCode=00&respMsg=success&settleAmt=1&settleCurrencyCode=156&settleDate=0908&signMethod=01&signPubKeyCert=-----BEGIN CERTIFICATE-----\r
MIIEYzCCA0ugAwIBAgIFEDkwhTQwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC\r
Q04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv\r
cml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMjAwNzMxMDExOTE2WhcN\r
MjUwNzMxMDExOTE2WjCBljELMAkGA1UEBhMCY24xEjAQBgNVBAoTCUNGQ0EgT0NB\r
MTEWMBQGA1UECxMNTG9jYWwgUkEgT0NBMTEUMBIGA1UECxMLRW50ZXJwcmlzZXMx\r
RTBDBgNVBAMMPDA0MUA4MzEwMDAwMDAwMDgzMDQwQOS4reWbvemTtuiBlOiCoeS7\r
veaciemZkOWFrOWPuEAwMDAxNjQ5NTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCC\r
AQoCggEBAMHNa81t44KBfUWUgZhb1YTx3nO9DeagzBO5ZEE9UZkdK5+2IpuYi48w\r
eYisCaLpLuhrwTced19w2UR5hVrc29aa2TxMvQH9s74bsAy7mqUJX+mPd6KThmCr\r
t5LriSQ7rDlD0MALq3yimLvkEdwYJnvyzA6CpHntP728HIGTXZH6zOL0OAvTnP8u\r
RCHZ8sXJPFUkZcbG3oVpdXQTJVlISZUUUhsfSsNdvRDrcKYY+bDWTMEcG8ZuMZzL\r
g0N+/spSwB8eWz+4P87nGFVlBMviBmJJX8u05oOXPyIcZu+CWybFQVcS2sMWDVZy\r
sPeT3tPuBDbFWmKQYuu+gT83PM3G6zMCAwEAAaOB9DCB8TAfBgNVHSMEGDAWgBTP\r
cJ1h6518Lrj3ywJA9wmd/jN0gDBIBgNVHSAEQTA/MD0GCGCBHIbvKgEBMDEwLwYI\r
KwYBBQUHAgEWI2h0dHA6Ly93d3cuY2ZjYS5jb20uY24vdXMvdXMtMTQuaHRtMDkG\r
A1UdHwQyMDAwLqAsoCqGKGh0dHA6Ly91Y3JsLmNmY2EuY29tLmNuL1JTQS9jcmw3\r
NTAwMy5jcmwwCwYDVR0PBAQDAgPoMB0GA1UdDgQWBBTmzk7XEM/J/sd+wPrMils3\r
9rJ2/DAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwDQYJKoZIhvcNAQEF\r
BQADggEBAJLbXxbJaFngROADdNmNUyVxPtbAvK32Ia0EjgDh/vjn1hpRNgvL4flH\r
NsGNttCy8afLJcH8UnFJyGLas8v/P3UKXTJtgrOj1mtothv7CQa4LUYhzrVw3UhL\r
4L1CTtmE6D1Kf3+c2Fj6TneK+MoK9AuckySjK5at6a2GQi18Y27gVF88Nk8bp1lJ\r
vzOwPKd8R7iGFotuF4/8GGhBKR4k46EYnKCodyIhNpPdQfpaN5AKeS7xeLSbFvPJ\r
HYrtBsI48jUK/WKtWBJWhFH+Gty+GWX0e5n2QHXHW6qH62M0lDo7OYeyBvG1mh9u\r
Q0C300Eo+XOoO4M1WvsRBAF13g9RPSw=\r
-----END CERTIFICATE-----&traceNo=067402&traceTime=0908132206&txnAmt=1&txnSubType=01&txnTime=20220908132206&txnType=01&version=5.1.0";
        $sign = 'JeA4S2+6TbGo9yjXDUvV5A2E3oJbunoCcZ66exN6xR3OH/5PNDK1VSV1Mq7XhVdxzkTeREUveiOYHalqoagRkh71nsHVvruwGbk6azygXSaawuO5tF67UIqNd4Mbufwh1KhbVpEkKbOETUvRhFcdon0fulE97I83eMSk52INHt8E1xk8NdbhyUadSlp+Uv30AKx70PpQbTGmVS3PJfd+Whj0b7LnvZKeC+BS1kUOtIKlcZO+gBoTigvCIJqj51kBrcBCs+x+VaeGm7EYBBhGSERpfQhQ4n+eJBwLdBeZ0/dNbo3iELjvVMx0n9KoW4klvUJhaH5LALA8pV02SbZv4Q==';

        verify_unipay_sign([], $contents, $sign);

        self::assertTrue(true);

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::UNIPAY_CONFIG_ERROR);
        self::expectExceptionMessage('Missing Unipay Config -- [unipay_public_cert_path]');
        Pay::get(ConfigInterface::class)->set('unipay.default.unipay_public_cert_path', null);
        verify_unipay_sign([], $contents, $sign);
    }
}
