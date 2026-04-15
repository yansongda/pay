<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;

class WechatTraitStub
{
    use WechatTrait;
}

class WechatTraitTest extends TestCase
{
    public function testGetWechatUrlRelativePath(): void
    {
        self::assertSame(
            \Yansongda\Pay\Provider\Wechat::URL[Pay::MODE_NORMAL].'v3/pay/transactions/jsapi',
            WechatTraitStub::getWechatUrl([], new Collection(['_url' => 'v3/pay/transactions/jsapi']))
        );
    }

    public function testGetWechatMethodDefault(): void
    {
        self::assertSame('POST', WechatTraitStub::getWechatMethod(null));
    }

    public function testVerifyWechatSignSuccess(): void
    {
        $timestamp = '1700000000';
        $nonce = 'nonce123';
        $body = '{"id":"test"}';
        $content = $timestamp."\n".$nonce."\n".$body."\n";

        $privateKey = openssl_pkey_get_private(file_get_contents(dirname(__DIR__).'/Cert/wechatAppPrivateKey.pem'));
        openssl_sign($content, $signature, $privateKey, 'sha256WithRSAEncryption');

        $request = (new ServerRequest('POST', 'https://example.com/wechat'))
            ->withHeader('Wechatpay-Serial', '45F59D4DABF31918AFCEC556D5D2C6E376675D57')
            ->withHeader('Wechatpay-Timestamp', $timestamp)
            ->withHeader('Wechatpay-Nonce', $nonce)
            ->withHeader('Wechatpay-Signature', base64_encode($signature))
            ->withBody(\GuzzleHttp\Psr7\Utils::streamFor($body));

        WechatTraitStub::verifyWechatSign($request, []);

        self::assertTrue(true);
    }
}
