<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use GuzzleHttp\Psr7\ServerRequest;

/**
 * 抖音支付回调 Helper。
 *
 * 构造带 Byte-Timestamp/Byte-Nonce-Str/Byte-Signature 头的回调请求，签名内容为 `timestamp\nnonce\nbody\n`。
 */
trait DouyinCallbackHelpers
{
    /**
     * @param array<string, string> $overrideHeaders
     */
    private function getDouyinCallbackRequest(string $body, array $overrideHeaders = []): ServerRequest
    {
        $headers = [
            'Byte-Timestamp' => '1700000000',
            'Byte-Nonce-Str' => 'abcdef1234567890',
            'Byte-Logid' => '202408041111312119',
            'Byte-Identifyname' => 'trade',
            'Byte-Signature' => base64_encode(
                $this->signDouyinContents("1700000000\nabcdef1234567890\n".$body."\n")
            ),
        ];

        return new ServerRequest('POST', 'https://yansongda.cn/douyin/notify', array_merge($headers, $overrideHeaders), $body);
    }

    private function signDouyinContents(string $contents): string
    {
        $privateKey = openssl_pkey_get_private(file_get_contents(cert_path('douyinPlatformPrivateKey.pem')));
        expect($privateKey)->not->toBeFalse();

        expect(openssl_sign($contents, $signature, $privateKey, OPENSSL_ALGO_SHA256))->toBeTrue();

        return $signature;
    }
}
