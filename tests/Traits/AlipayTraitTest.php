<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AlipayV2Config;
use Yansongda\Pay\Config\AlipayV3Config;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\AlipayTrait;
use Yansongda\Supports\Collection;

class AlipayTraitStub
{
    use AlipayTrait;
}

class AlipayTraitTest extends TestCase
{
    public function testVerifyAlipaySignSuccess(): void
    {
        AlipayTraitStub::getProviderConfig('alipay');

        AlipayTraitStub::verifyAlipaySign(
            AlipayTraitStub::getProviderConfig('alipay'),
            json_encode([
                'code' => '10000',
                'msg' => 'Success',
                'order_id' => '20231220110070000002150000657610',
                'out_biz_no' => '2023122022560000',
                'pay_date' => '2023-12-20 22:56:33',
                'pay_fund_order_id' => '20231220110070001502150000660902',
                'status' => 'SUCCESS',
                'trans_amount' => '0.01',
            ], JSON_UNESCAPED_UNICODE),
            'eITxP5fZiJPB2+vZb90IRkv2iARxeNx/6Omxk7FStqflhG5lMoCvGjo2FZ6Szo1bGBMBReazZuqLaqsgomWAUO9onMVurB3enLbRvwUlpE7XEZaxk/sJYjgc2Y7pIAenvnLL9PEAiXmvUvuinUlvS9J2r1XysC0p/2wu7kEJ/GgZpFDIIYY9mdM6U1rGbi+RvirQXtQHmaEuuJWLA75NR1bvfG3L8znzW9xz1kOQqOWsQmD/bF1CDWbozNLwLCUmClRJz0Fj4mUYRF0zbW2VP8ZgHu1YvVKJ2+dWC9b+0o94URk7psIpc5NjiOM9Jsn6aoC2CfrJ/sqFMRCkYWzw6A=='
        );
        self::assertTrue(true);
    }

    public function testVerifyAlipaySignConfigError(): void
    {
        $config1 = [
            'alipay' => [
                'default' => [
                    'alipay_public_cert_path' => '',
                ],
            ],
        ];

        Pay::config(array_merge($config1, ['_force' => true]));

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);

        AlipayTraitStub::getProviderConfig('alipay');
    }

    public function testVerifyAlipaySignEmpty(): void
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);
        AlipayTraitStub::verifyAlipaySign(AlipayTraitStub::getProviderConfig('alipay'), '', '');
    }

    public function testGetAlipayUrlDefault(): void
    {
        self::assertSame(
            Alipay::URL[Pay::MODE_NORMAL],
            AlipayTraitStub::getAlipayUrl($this->getAlipayConfig(), null)
        );
    }

    public function testGetAlipayUrlSandbox(): void
    {
        self::assertSame(
            Alipay::URL[Pay::MODE_SANDBOX],
            AlipayTraitStub::getAlipayUrl($this->getAlipayConfig(Pay::MODE_SANDBOX), null)
        );
    }

    public function testGetAlipayUrlWithPayload(): void
    {
        self::assertSame(
            'https://example.com/alipay',
            AlipayTraitStub::getAlipayUrl($this->getAlipayConfig(), new Collection(['_url' => 'https://example.com/alipay']))
        );
    }

    protected function getAlipayConfig(int $mode = Pay::MODE_NORMAL): AlipayV2Config
    {
        return new AlipayV2Config([
            'app_id' => 'app_id',
            'app_secret_cert' => 'app_secret_cert',
            'app_public_cert_path' => 'app_public_cert_path',
            'alipay_public_cert_path' => 'alipay_public_cert_path',
            'alipay_root_cert_path' => 'alipay_root_cert_path',
            'mode' => $mode,
        ], 'default');
    }

    protected function getAlipayV3Config(int $mode = Pay::MODE_NORMAL): AlipayV3Config
    {
        return new AlipayV3Config([
            'app_id' => 'app_id',
            'app_secret_cert' => 'app_secret_cert',
            'alipay_public_key' => 'alipay_public_key',
            'mode' => $mode,
        ], 'default');
    }

    public function testGetAlipayV3UrlDefault(): void
    {
        self::assertSame(
            Alipay::V3_URL[Pay::MODE_NORMAL],
            AlipayTraitStub::getAlipayV3Url($this->getAlipayV3Config(), null)
        );
    }

    public function testGetAlipayV3UrlSandbox(): void
    {
        self::assertSame(
            Alipay::V3_URL[Pay::MODE_SANDBOX],
            AlipayTraitStub::getAlipayV3Url($this->getAlipayV3Config(Pay::MODE_SANDBOX), null)
        );
    }

    public function testGetAlipayV3UrlWithPayloadFullUrl(): void
    {
        self::assertSame(
            'https://example.com/v3/alipay/trade/pay',
            AlipayTraitStub::getAlipayV3Url($this->getAlipayV3Config(), new Collection(['_url' => 'https://example.com/v3/alipay/trade/pay']))
        );
    }

    public function testGetAlipayV3UrlWithPayloadPath(): void
    {
        self::assertSame(
            Alipay::V3_URL[Pay::MODE_NORMAL].'/v3/alipay/trade/pay',
            AlipayTraitStub::getAlipayV3Url($this->getAlipayV3Config(), new Collection(['_url' => '/v3/alipay/trade/pay']))
        );
    }

    public function testGetAlipayV3AuthorizationPublicKeyMode(): void
    {
        $config = AlipayTraitStub::getProviderConfig('alipay', ['_config' => 'alipay-v3']);

        $authorization = AlipayTraitStub::getAlipayV3Authorization($config, 'POST', '/v3/alipay/trade/pay', '{"out_trade_no":"123"}');

        self::assertStringStartsWith('ALIPAY-SHA256withRSA app_id=alipay_v3_test_app_id,', $authorization);
        self::assertStringContainsString(',nonce=', $authorization);
        self::assertStringContainsString(',timestamp=', $authorization);
        self::assertStringNotContainsString(',app_cert_sn=', $authorization);

        $authString = self::getAuthString($authorization);
        $sign = self::getSign($authorization);

        $content = $authString."\n"
            .'POST'."\n"
            .'/v3/alipay/trade/pay'."\n"
            .'{"out_trade_no":"123"}'."\n";

        $publicKey = openssl_pkey_get_public(
            "-----BEGIN PUBLIC KEY-----\n".wordwrap((string) $config->getAlipayPublicKey(), 64, "\n", true)."\n-----END PUBLIC KEY-----"
        );

        self::assertSame(1, openssl_verify($content, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256));
    }

    public function testGetAlipayV3AuthorizationCertMode(): void
    {
        $config = AlipayTraitStub::getProviderConfig('alipay', ['_config' => 'alipay-v3-cert']);

        $authorization = AlipayTraitStub::getAlipayV3Authorization($config, 'POST', '/v3/alipay/trade/query', '', 'test_app_auth_token');

        self::assertStringContainsString(',app_cert_sn=', $authorization);
        self::assertStringContainsString('app_id=alipay_v3_test_app_id,', $authorization);

        $authString = self::getAuthString($authorization);
        $sign = self::getSign($authorization);

        // 空 body 保留空行，appAuthToken 非空则作为第 5 行
        $content = $authString."\n"
            .'POST'."\n"
            .'/v3/alipay/trade/query'."\n"
            ."\n"
            .'test_app_auth_token'."\n";

        $publicKey = openssl_pkey_get_public(file_get_contents(__DIR__.'/../Cert/alipay-v3/alipay_public_cert_test.crt'));

        self::assertNotFalse($publicKey);
        self::assertSame(1, openssl_verify($content, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256));
    }

    public function testVerifyAlipayV3SignSuccess(): void
    {
        $config = AlipayTraitStub::getProviderConfig('alipay', ['_config' => 'alipay-v3']);
        $contents = "1666004496123\nyansongda-nonce\n{\"code\":\"10000\"}\n";

        openssl_sign($contents, $sign, openssl_pkey_get_private(file_get_contents(__DIR__.'/../Cert/alipay-v3/app_secret_test.pem')), OPENSSL_ALGO_SHA256);

        AlipayTraitStub::verifyAlipayV3Sign($config, $contents, base64_encode($sign));

        self::assertTrue(true);
    }

    public function testVerifyAlipayV3SignCertModeSuccess(): void
    {
        $config = AlipayTraitStub::getProviderConfig('alipay', ['_config' => 'alipay-v3-cert']);
        $contents = "1666004496123\nyansongda-nonce\n{\"code\":\"10000\"}\n";

        openssl_sign($contents, $sign, openssl_pkey_get_private(file_get_contents(__DIR__.'/../Cert/alipay-v3/app_secret_test.pem')), OPENSSL_ALGO_SHA256);

        AlipayTraitStub::verifyAlipayV3Sign($config, $contents, base64_encode($sign));

        self::assertTrue(true);
    }

    public function testVerifyAlipayV3SignError(): void
    {
        $config = AlipayTraitStub::getProviderConfig('alipay', ['_config' => 'alipay-v3']);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AlipayTraitStub::verifyAlipayV3Sign($config, "1666004496123\nyansongda-nonce\n{}\n", 'invalid-sign');
    }

    public function testVerifyAlipayV3SignEmpty(): void
    {
        $config = AlipayTraitStub::getProviderConfig('alipay', ['_config' => 'alipay-v3']);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        AlipayTraitStub::verifyAlipayV3Sign($config, "1666004496123\nyansongda-nonce\n{}\n", '');
    }

    public function testVerifyAlipayV3SignMissingAlipayPublicKey(): void
    {
        $config = new AlipayV3Config([
            'app_id' => 'app_id',
            'app_secret_cert' => 'app_secret_cert',
        ], 'alipay-v3-no-public-key');

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);
        self::expectExceptionMessage('配置异常: 缺少支付宝配置 -- [alipay_public_key]');

        AlipayTraitStub::verifyAlipayV3Sign($config, "1666004496123\nyansongda-nonce\n{}\n", 'not-empty-sign');
    }

    public function testVerifyAlipayV3TimestampValid(): void
    {
        AlipayTraitStub::verifyAlipayV3Timestamp((string) (time() * 1000));

        self::assertTrue(true);
    }

    public function testVerifyAlipayV3TimestampExpired(): void
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);
        self::expectExceptionMessage('签名异常: 支付宝 V3 时间戳已过期');

        AlipayTraitStub::verifyAlipayV3Timestamp('1735689600123');
    }

    private static function getAuthString(string $authorization): string
    {
        $rest = substr($authorization, strlen('ALIPAY-SHA256withRSA '));

        return substr($rest, 0, (int) strrpos($rest, ',sign='));
    }

    private static function getSign(string $authorization): string
    {
        return substr($authorization, (int) strrpos($authorization, ',sign=') + strlen(',sign='));
    }
}
