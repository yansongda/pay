<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Pay\Config\DouyinConfig;

/**
 * 抖音 Trait 测试 Helper。
 *
 * 回调请求按抖音开放平台规则签名：`timestamp\nnonce\nbody\n`，平台私钥见 tests/Cert。
 */
trait DouyinTraitHelpers
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
     * @param array<string, string> $overrideHeaders
     */
    private function getDouyinCallbackRequest(string $body, array $overrideHeaders = [], ?string $signBody = null): ServerRequest
    {
        $signBody = $signBody ?? $body;

        $headers = [
            'Byte-Timestamp' => '1700000000',
            'Byte-Nonce-Str' => 'abcdef1234567890',
            'Byte-Signature' => base64_encode(
                $this->signDouyinContents("1700000000\nabcdef1234567890\n".$signBody."\n", file_get_contents(cert_path('douyinPlatformPrivateKey.pem')))
            ),
        ];

        return new ServerRequest('POST', 'https://yansongda.cn/douyin/notify', array_merge($headers, $overrideHeaders), $body);
    }

    private function signDouyinContents(string $contents, string $privateKeyContents): string
    {
        $privateKey = openssl_pkey_get_private($privateKeyContents);
        expect($privateKey)->not->toBeFalse();

        expect(openssl_sign($contents, $signature, $privateKey, OPENSSL_ALGO_SHA256))->toBeTrue();

        return $signature;
    }
}
