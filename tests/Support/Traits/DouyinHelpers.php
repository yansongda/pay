<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Pay\Config\DouyinConfig;

/**
 * 抖音支付测试 Helper。
 *
 * 回调请求按抖音开放平台规则签名：`timestamp\nnonce\nbody\n`（平台私钥见 tests/Cert），
 * 统一提供 DouyinConfig 构造、回调请求构造与内容签名，覆盖 Trait / Plugin / Provider 三层用例。
 */
trait DouyinHelpers
{
    private function getDouyinConfig(): DouyinConfig
    {
        return new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
            'app_private_key' => file_get_contents(cert_path('douyinAppPrivateKey.pem')),
            'douyin_public_key' => file_get_contents(cert_path('douyinPlatformPublicKey.pem')),
        ]);
    }

    /**
     * @param array<string, string> $overrideHeaders 覆盖/追加请求头
     * @param null|string           $signBody        签名内容与 body 不一致的场景（反路径用例）
     * @param null|string           $privateKeyContents 签名私钥内容，默认平台私钥
     */
    private function getDouyinCallbackRequest(string $body, array $overrideHeaders = [], ?string $signBody = null, ?string $privateKeyContents = null): ServerRequest
    {
        $signBody = $signBody ?? $body;

        $headers = [
            'Byte-Timestamp' => '1700000000',
            'Byte-Nonce-Str' => 'abcdef1234567890',
            'Byte-Logid' => '202408041111312119',
            'Byte-Identifyname' => 'trade',
            'Byte-Signature' => base64_encode(
                $this->signDouyinContents("1700000000\nabcdef1234567890\n".$signBody."\n", $privateKeyContents)
            ),
        ];

        return new ServerRequest('POST', 'https://yansongda.cn/douyin/notify', array_merge($headers, $overrideHeaders), $body);
    }

    private function signDouyinContents(string $contents, ?string $privateKeyContents = null): string
    {
        $privateKey = openssl_pkey_get_private($privateKeyContents ?? file_get_contents(cert_path('douyinPlatformPrivateKey.pem')));
        expect($privateKey)->not->toBeFalse();

        expect(openssl_sign($contents, $signature, $privateKey, OPENSSL_ALGO_SHA256))->toBeTrue();

        return $signature;
    }
}
