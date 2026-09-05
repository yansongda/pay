<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\DouyinTrait;
use Yansongda\Supports\Collection;

class DouyinTraitStub
{
    use DouyinTrait;
}

class DouyinTraitTest extends TestCase
{
    public function testGetDouyinUrl(): void
    {
        $normalConfig = new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
            'app_private_key' => 'douyin_app_private_key',
            'platform_public_key' => 'douyin_platform_public_key',
        ]);
        $serviceConfig = new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
            'app_private_key' => 'douyin_app_private_key',
            'platform_public_key' => 'douyin_platform_public_key',
            'mode' => Pay::MODE_SERVICE,
        ]);

        self::assertEquals('https://yansongda.cn', DouyinTraitStub::getDouyinUrl($normalConfig, new Collection(['_url' => 'https://yansongda.cn'])));
        self::assertEquals('https://open.douyin.com/api/v1/yansongda', DouyinTraitStub::getDouyinUrl($normalConfig, new Collection(['_url' => '/api/v1/yansongda'])));
        self::assertEquals('https://open.douyin.com/api/v1/service/yansongda', DouyinTraitStub::getDouyinUrl($serviceConfig, new Collection(['_service_url' => '/api/v1/service/yansongda'])));
        self::assertEquals('https://open.douyin.com/api/v1/service/yansongda', DouyinTraitStub::getDouyinUrl($serviceConfig, new Collection(['_url' => '/foo', '_service_url' => '/api/v1/service/yansongda'])));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_DOUYIN_URL_MISSING);
        DouyinTraitStub::getDouyinUrl($normalConfig, new Collection([]));
    }

    public function testGetDouyinTradeSign(): void
    {
        $config = $this->getDouyinConfig();
        $timestamp = '1700000000';
        $nonce = 'abcdef1234567890';
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $header = DouyinTraitStub::getDouyinTradeSign($config, 'POST', '/api/v1/trade/create', $body, $timestamp, $nonce);

        self::assertSame(
            'SHA256-RSA2048 appid="tt226e54d3bd581bf801",nonce_str="abcdef1234567890",timestamp="1700000000",key_version=1,signature="'
            .base64_encode($this->signDouyinContents("POST\n/api/v1/trade/create\n1700000000\nabcdef1234567890\n".$body."\n", file_get_contents(__DIR__.'/../Cert/douyinAppPrivateKey.pem')))
            .'"',
            $header
        );
    }

    public function testGetDouyinTradeSignRoundTrip(): void
    {
        $config = $this->getDouyinConfig();
        $timestamp = '1700000000';
        $nonce = 'abcdef1234567890';
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $header = DouyinTraitStub::getDouyinTradeSign($config, 'POST', '/api/v1/trade/create', $body, $timestamp, $nonce);

        preg_match('/signature="([^"]+)"$/', $header, $matches);
        self::assertNotEmpty($matches);

        $publicKey = openssl_pkey_get_public(file_get_contents(__DIR__.'/../Cert/douyinAppPublicKey.pem'));
        self::assertNotFalse($publicKey);

        self::assertSame(
            1,
            openssl_verify("POST\n/api/v1/trade/create\n1700000000\nabcdef1234567890\n".$body."\n", base64_decode($matches[1]), $publicKey, OPENSSL_ALGO_SHA256)
        );
    }

    public function testGetDouyinTradeSignInvalidPrivateKey(): void
    {
        $config = new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
            'app_private_key' => 'invalid_private_key',
        ]);

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_DOUYIN_INVALID);

        DouyinTraitStub::getDouyinTradeSign($config, 'POST', '/api/v1/trade/create', '{}', '1700000000', 'abcdef1234567890');
    }

    public function testVerifyDouyinTradeSign(): void
    {
        $config = $this->getDouyinConfig();
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $request = $this->getDouyinCallbackRequest($body);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);

        self::assertTrue(true);
    }

    public function testVerifyDouyinTradeSignTamperedBody(): void
    {
        $config = $this->getDouyinConfig();

        // 用原始 body 签名，但请求体被篡改
        $request = $this->getDouyinCallbackRequest('{"out_trade_no":"yansongda","total_amount":100}', [], '{"out_trade_no":"yansongda","total_amount":1}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    }

    public function testVerifyDouyinTradeSignTamperedSign(): void
    {
        $config = $this->getDouyinConfig();
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $request = $this->getDouyinCallbackRequest($body, ['Byte-Signature' => base64_encode('tampered-signature')]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    }

    public function testVerifyDouyinTradeSignEmptyTimestamp(): void
    {
        $config = $this->getDouyinConfig();
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $request = $this->getDouyinCallbackRequest($body, ['Byte-Timestamp' => '']);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    }

    public function testVerifyDouyinTradeSignEmptyNonce(): void
    {
        $config = $this->getDouyinConfig();
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $request = $this->getDouyinCallbackRequest($body, ['Byte-Nonce-Str' => '']);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    }

    public function testVerifyDouyinTradeSignEmptySign(): void
    {
        $config = $this->getDouyinConfig();
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $request = $this->getDouyinCallbackRequest($body, ['Byte-Signature' => '']);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    }

    public function testVerifyDouyinTradeSignEmptyBody(): void
    {
        $config = $this->getDouyinConfig();

        $request = $this->getDouyinCallbackRequest('');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    }

    public function testVerifyDouyinTradeSignInvalidPlatformPublicKey(): void
    {
        $config = new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
            'platform_public_key' => 'invalid_public_key',
        ]);

        $request = $this->getDouyinCallbackRequest('{"out_trade_no":"yansongda","total_amount":1}');

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_DOUYIN_INVALID);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    }

    private function getDouyinConfig(): DouyinConfig
    {
        return new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
            'app_private_key' => file_get_contents(__DIR__.'/../Cert/douyinAppPrivateKey.pem'),
            'platform_public_key' => file_get_contents(__DIR__.'/../Cert/douyinPlatformPublicKey.pem'),
        ]);
    }

    /**
     * @param array<string, string> $overrideHeaders
     */
    private function getDouyinCallbackRequest(string $body, array $overrideHeaders = [], ?string $signBody = null): ServerRequest
    {
        $signBody = $signBody ?? $body;

        $headers = [
            'Byte-Timestamp' => '1700000000',
            'Byte-Nonce-Str' => 'abcdef1234567890',
            'Byte-Signature' => base64_encode(
                $this->signDouyinContents("1700000000\nabcdef1234567890\n".$signBody."\n", file_get_contents(__DIR__.'/../Cert/douyinPlatformPrivateKey.pem'))
            ),
        ];

        return new ServerRequest('POST', 'https://yansongda.cn/douyin/notify', array_merge($headers, $overrideHeaders), $body);
    }

    private function signDouyinContents(string $contents, string $privateKeyContents): string
    {
        $privateKey = openssl_pkey_get_private($privateKeyContents);
        self::assertNotFalse($privateKey);

        self::assertTrue(openssl_sign($contents, $signature, $privateKey, OPENSSL_ALGO_SHA256));

        return $signature;
    }
}
