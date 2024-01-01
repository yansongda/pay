<?php

namespace Yansongda\Pay\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Direction\CollectionDirection;
use Yansongda\Pay\Direction\NoHttpRequestDirection;
use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;
use function Yansongda\Pay\decrypt_wechat_contents;
use function Yansongda\Pay\decrypt_wechat_resource;
use function Yansongda\Pay\decrypt_wechat_resource_aes_256_gcm;
use function Yansongda\Pay\encrypt_wechat_contents;
use function Yansongda\Pay\filter_params;
use function Yansongda\Pay\get_alipay_config;
use function Yansongda\Pay\get_direction;
use function Yansongda\Pay\get_private_cert;
use function Yansongda\Pay\get_public_cert;
use function Yansongda\Pay\get_radar_body;
use function Yansongda\Pay\get_radar_method;
use function Yansongda\Pay\get_radar_url;
use function Yansongda\Pay\get_tenant;
use function Yansongda\Pay\get_unipay_body;
use function Yansongda\Pay\get_unipay_config;
use function Yansongda\Pay\get_unipay_url;
use function Yansongda\Pay\get_wechat_body;
use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_method;
use function Yansongda\Pay\get_wechat_public_certs;
use function Yansongda\Pay\get_wechat_public_key;
use function Yansongda\Pay\get_wechat_serial_no;
use function Yansongda\Pay\get_wechat_sign;
use function Yansongda\Pay\get_wechat_sign_v2;
use function Yansongda\Pay\get_wechat_type_key;
use function Yansongda\Pay\get_wechat_url;
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

        Pay::set(DirectionInterface::class, CollectionDirection::class);
        Pay::set(PackerInterface::class, JsonPacker::class);
    }

    public function testShouldDoHttpRequest()
    {
        $rocket = new Rocket();

        self::assertTrue(should_do_http_request($rocket->getDirection()));

        $rocket->setDirection(CollectionDirection::class);
        self::assertTrue(should_do_http_request($rocket->getDirection()));

        $rocket->setDirection(ResponseDirection::class);
        self::assertFalse(should_do_http_request($rocket->getDirection()));

        $rocket->setDirection(NoHttpRequestDirection::class);
        self::assertFalse(should_do_http_request($rocket->getDirection()));
    }

    public function testGetTenant()
    {
        self::assertEquals('default', get_tenant([]));
        self::assertEquals('yansongda', get_tenant(['_config' => 'yansongda']));
    }

    public function testGetDirection()
    {
        self::assertInstanceOf(DirectionInterface::class, get_direction(DirectionInterface::class));

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_DIRECTION_INVALID);
        get_direction('invalid');
    }

    public function testGetPublicCert()
    {
        $alipayPublicCertPath = __DIR__ . '/Cert/alipayPublicCert.crt';
        $alipayPublicCertCerPath = __DIR__ . '/Cert/alipayPublicCert.cer';

        self::assertEquals(file_get_contents($alipayPublicCertCerPath), get_public_cert($alipayPublicCertCerPath));
        self::assertEquals(file_get_contents($alipayPublicCertPath), get_public_cert($alipayPublicCertPath));
    }

    public function testGetPrivateCert()
    {
        $appSecretCert = file_get_contents(__DIR__ . '/Cert/alipayAppSecret.txt');

        self::assertTrue(Str::contains(get_private_cert($appSecretCert), 'PRIVATE KEY'));
    }

    public function testFilterParams()
    {
        // none closure
        $params = [
            'name' => 'yansongda',
            '_method' => 'foo',
            'a' => null,
        ];
        self::assertEqualsCanonicalizing(['name' => 'yansongda'], filter_params($params));

        // closure
        $params = [
            'name' => 'yansongda',
            '_method' => 'foo',
            'sign' => 'aaa',
            'sign_type' => 'bbb',
            's' => '',
            'a' => null,
        ];
        self::assertEqualsCanonicalizing(['name' => 'yansongda'], filter_params($params, fn ($k, $v) => '' !== $v && !is_null($v) && 'sign' != $k && 'sign_type' != $k));
    }

    public function testGetRadarMethod()
    {
        self::assertNull(get_radar_method(null));
        self::assertNull(get_radar_method(new Collection()));
        self::assertEquals('GET', get_radar_method(new Collection(['_method' => 'get'])));
        self::assertEquals('POST', get_radar_method(new Collection(['_method' => 'post'])));
    }

    public function testGetRadarUrl()
    {
        self::assertNull(get_radar_url([], null));
        self::assertNull(get_radar_url([], new Collection()));
        self::assertEquals('https://yansongda.cn', get_radar_url([], new Collection(['_url' => 'https://yansongda.cn'])));
        self::assertEquals('https://yansongda.cnaaa', get_radar_url(['mode' => Pay::MODE_SERVICE], new Collection(['_url' => 'https://yansongda.cn', '_service_url' => 'https://yansongda.cnaaa'])));
    }

    public function testGetRadarBody()
    {
        self::assertNull(get_radar_body(new Collection([])));
        self::assertNull(get_radar_body(null));

        self::assertEquals('https://yansongda.cn', get_wechat_body(new Collection(['_body' => 'https://yansongda.cn'])));
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

    public function testVerifyAlipaySign()
    {
        verify_alipay_sign(get_alipay_config(), json_encode([
            "code" => "10000",
            "msg" => "Success",
            "order_id" => "20231220110070000002150000657610",
            "out_biz_no" => "2023122022560000",
            "pay_date" => "2023-12-20 22:56:33",
            "pay_fund_order_id" => "20231220110070001502150000660902",
            "status" => "SUCCESS",
            "trans_amount" => "0.01",
        ], JSON_UNESCAPED_UNICODE), 'eITxP5fZiJPB2+vZb90IRkv2iARxeNx/6Omxk7FStqflhG5lMoCvGjo2FZ6Szo1bGBMBReazZuqLaqsgomWAUO9onMVurB3enLbRvwUlpE7XEZaxk/sJYjgc2Y7pIAenvnLL9PEAiXmvUvuinUlvS9J2r1XysC0p/2wu7kEJ/GgZpFDIIYY9mdM6U1rGbi+RvirQXtQHmaEuuJWLA75NR1bvfG3L8znzW9xz1kOQqOWsQmD/bF1CDWbozNLwLCUmClRJz0Fj4mUYRF0zbW2VP8ZgHu1YvVKJ2+dWC9b+0o94URk7psIpc5NjiOM9Jsn6aoC2CfrJ/sqFMRCkYWzw6A==');
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
        self::expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);
        verify_alipay_sign(get_alipay_config(), '', 'aaa');
    }

    public function testVerifyAlipaySignEmpty()
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);
        verify_alipay_sign(get_alipay_config(), '', '');
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

    public function testGetWechatMethod()
    {
        self::assertEquals('POST', get_wechat_method(null));
        self::assertEquals('GET', get_wechat_method(new Collection(['_method' => 'get'])));
        self::assertEquals('POST', get_wechat_method(new Collection(['_method' => 'post'])));
    }

    public function testGetWechatUrl()
    {
        self::assertEquals('https://yansongda.cn', get_wechat_url([], new Collection(['_url' => 'https://yansongda.cn'])));
        self::assertEquals('https://api.mch.weixin.qq.com/api/v1/yansongda', get_wechat_url([], new Collection(['_url' => 'api/v1/yansongda'])));
        self::assertEquals('https://api.mch.weixin.qq.com/api/v1/service/yansongda', get_wechat_url(['mode' => Pay::MODE_SERVICE], new Collection(['_service_url' => 'api/v1/service/yansongda'])));
        self::assertEquals('https://api.mch.weixin.qq.com/api/v1/service/yansongda', get_wechat_url(['mode' => Pay::MODE_SERVICE], new Collection(['_url' => 'foo', '_service_url' => 'api/v1/service/yansongda'])));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_WECHAT_URL_MISSING);
        get_wechat_url([], new Collection([]));
    }

    public function testGetWechatBody()
    {
        self::assertEquals('https://yansongda.cn', get_wechat_body(new Collection(['_body' => 'https://yansongda.cn'])));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_WECHAT_BODY_MISSING);
        get_wechat_body(new Collection([]));
    }

    public function testGetWechatConfigKey()
    {
        // default
        self::assertEquals('mp_app_id', get_wechat_type_key([]));
        // app
        self::assertEquals('app_id', get_wechat_type_key(['_type' => 'app']));
        // mini
        self::assertEquals('mini_app_id', get_wechat_type_key(['_type' => 'mini']));
    }

    public function testGetWechatSign()
    {
        $contents = "POST\n/v3/pay/transactions/h5\n1626493236\nQqtzdVzxavZeXag9G5mtfzbfzFMf89p6\n{\"out_trade_no\":1626493236,\"description\":\"yansongda 测试 - 1626493236\",\"amount\":{\"total\":1},\"scene_info\":{\"payer_client_ip\":\"127.0.0.1\",\"h5_info\":{\"type\":\"Wap\"}},\"appid\":\"wx55955316af4ef13\",\"mchid\":\"1600314069\",\"notify_url\":\"http:\/\/127.0.0.1:8000\/wechat\/notify\"}\n";

        self::assertEquals(
            'KzIgMgiop3nQJNdBVR2Xah/JUwVBLDFFajyXPiSN8b8YAYEA4FuWfaCgFJ52+WFed+PhOYWx/ZPih4RaEuuSdYB8eZwYUx7RZGMQZk0bKCctAjjPuf4pJN+f/WsXKjPIy3diqF5x7gyxwSCaKWP4/KjsHNqgQpiC8q1uC5xmElzuhzSwj88LIoLtkAuSmtUVvdAt0Nz41ECHZgHWSGR32TfBo902r8afdaVKkFde8IoqcEJJcp6sMxdDO5l9R5KEWxrJ1SjsXVrb0IPH8Nj7e6hfhq7pucxojPpzsC+ZWAYvufZkAQx3kTiFmY87T+QhkP9FesOfWvkIRL4E6MP6ug==',
            get_wechat_sign(get_wechat_config([]), $contents)
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
        self::expectExceptionCode(Exception::CONFIG_WECHAT_INVALID);
        get_wechat_sign([], '', '');
    }

    public function testGetWechatSignV2()
    {
        $params = ['name' => 'yansongda', 'age' => 29, 'foo' => ''];
        self::assertEquals('3213848AED2C380749FD1D559555881D', get_wechat_sign_v2([], $params));

        // test config error
        $config1 = [
            'wechat' => [
                'default' => [
                    'mch_secret_key_v2' => ''
                ],
            ]
        ];
        Pay::config(array_merge($config1, ['_force' => true]));

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_WECHAT_INVALID);
        get_wechat_sign_v2([], []);
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

    public function testDecryptWechatContents()
    {
        $encrypted = 'WIesmK+dSJycwdhTTkNmv0Lk2wb9o7NGODovccjhyotNnRkEeh+sxRK1gNSRNMJJgkQ30m4HwcuweSO24mehFeXVNTVAKFVef/3FlHnYDZfE1c3mCLToEef7e8J/Z8TwFH1ecn3t+Jk9ZaBpQKNHdQ0Q8jcL7AnL48h0D9BcZxDekPqX6hNnKfISoKSv4TXFcgvBLFeAe4Q3KM0Snq0N5IvI86D9xZqVg6mY+Gfz0782ymQFxflau6Qxx3mJ+0etHMocNuCdgctVH390XYYMc0u+V2FCJ5cU5h/M/AxzP9ayrEO4l0ftaxL6lP0HjifNrkPcAAb+q9I67UepKO9iGw==';

        $config = get_wechat_config();

        self::assertEquals('yansongda', decrypt_wechat_contents($encrypted, $config));
        self::assertNull(decrypt_wechat_contents('invalid', $config));
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

        self::assertTrue(str_contains($result, '-----BEGIN CERTIFICATE-----'));
        self::assertTrue(Pay::get(ConfigInterface::class)->has('wechat.default.wechat_public_cert_path.test-45F59D4DABF31918AFCEC556D5D2C6E376675D57'));
        self::assertIsArray(Pay::get(ConfigInterface::class)->get('wechat.default'));
    }

    public function testGetWechatPublicCerts()
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

        $path = sys_get_temp_dir();

        get_wechat_public_certs([], $path);

        $crtPathName = $path . '/test-45F59D4DABF31918AFCEC556D5D2C6E376675D57.crt';

        self::assertFileExists($crtPathName);
        self::assertTrue(str_contains(file_get_contents($crtPathName), '-----BEGIN CERTIFICATE-----'));

        self::expectOutputRegex('/.*-----BEGIN CERTIFICATE-----/');
        get_wechat_public_certs();
    }

    public function testDecryptWechatResource()
    {
        $resource = [
            'algorithm' => 'AEAD_AES_256_GCM',
            'associated_data' => 'certificate',
            'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
            'nonce' => '4196a5b75276',
        ];

        $result = decrypt_wechat_resource($resource, get_wechat_config());

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
        self::expectException(DecryptException::class);
        self::expectExceptionCode(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID);
        decrypt_wechat_resource_aes_256_gcm(base64_decode($resource['ciphertext']), 'foo', $resource['nonce'], $resource['associated_data']);
    }

    public function testGetWechatSerialNo()
    {
        // 不传证书
        $params = [];
        $result = get_wechat_serial_no($params);
        self::assertTrue(in_array($result, ['45F59D4DABF31918AFCEC556D5D2C6E376675D57', 'yansongda']));

        // 传证书
        $params = ['_serial_no' => 'yansongda',];
        $result = get_wechat_serial_no($params);
        self::assertEquals('yansongda', $result);
    }

    public function testGetWechatSerialNoWithRequestWechat()
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

        $params = ['_config' => 'empty_wechat_public_cert'];
        $result = get_wechat_serial_no($params);
        self::assertEquals('test-45F59D4DABF31918AFCEC556D5D2C6E376675D57', $result);
    }

    public function testGetWechatPublicKey()
    {
        $serialNo = '45F59D4DABF31918AFCEC556D5D2C6E376675D57';
        $result = get_wechat_public_key(get_wechat_config(), $serialNo);
        self::assertIsString($result);

        $serialNo = 'non-exist';
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_WECHAT_SERIAL_NOT_FOUND);
        get_wechat_public_key(get_wechat_config(), $serialNo);
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

        verify_unipay_sign(get_unipay_config(), $contents, $sign);

        self::assertTrue(true);

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_UNIPAY_INVALID);
        self::expectExceptionMessage('配置异常： 缺少银联配置 -- [unipay_public_cert_path]');
        Pay::get(ConfigInterface::class)->set('unipay.default.unipay_public_cert_path', null);
        verify_unipay_sign([], $contents, $sign);
    }

    public function testVerifyUnipaySignEmpty()
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);
        self::expectExceptionMessage('签名异常: 银联签名为空');
        verify_unipay_sign([], '', '');
    }

    public function testGetUnipayUrl()
    {
        self::assertEquals('https://yansongda.cn', get_wechat_url([], new Collection(['_url' => 'https://yansongda.cn'])));
        self::assertEquals('https://api.mch.weixin.qq.com/api/v1/yansongda', get_wechat_url([], new Collection(['_url' => 'api/v1/yansongda'])));
        self::assertEquals('https://api.mch.weixin.qq.com/api/v1/service/yansongda', get_wechat_url(['mode' => Pay::MODE_SERVICE], new Collection(['_service_url' => 'api/v1/service/yansongda'])));
        self::assertEquals('https://api.mch.weixin.qq.com/api/v1/service/yansongda', get_wechat_url(['mode' => Pay::MODE_SERVICE], new Collection(['_url' => 'foo', '_service_url' => 'api/v1/service/yansongda'])));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_UNIPAY_URL_MISSING);
        get_unipay_url([], new Collection([]));
    }

    public function testGetUnipayBody()
    {
        self::assertEquals('https://yansongda.cn', get_wechat_body(new Collection(['_body' => 'https://yansongda.cn'])));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_UNIPAY_BODY_MISSING);
        get_unipay_body(new Collection([]));
    }
}
