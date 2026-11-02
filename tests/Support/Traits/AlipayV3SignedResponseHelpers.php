<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\CertManager;

/**
 * 支付宝 V3 签名响应 Helper。
 *
 * 生成带签名的模拟响应（签名密钥与 alipay-v3 测试租户支付宝公钥同属一对密钥）.
 *
 * 用 `alipay-v3` 测试租户私钥按 `timestamp\nnonce\nbody\n` 组串签名，构造带
 * `alipay-timestamp`/`alipay-nonce`/`alipay-signature`/`alipay-sn` 头的 mock HTTP 响应，
 * 供 Alipay V3 Shortcut 用例走完整「验签 → 解析」链路。
 */
trait AlipayV3SignedResponseHelpers
{
    private function makeSignedResponse(int $status, string $body): Response
    {
        $timestamp = (string) (int) (microtime(true) * 1000);
        $nonce = 'yansongda-nonce';
        openssl_sign($timestamp."\n".$nonce."\n".$body."\n", $sign, openssl_pkey_get_private(file_get_contents(cert_path('alipay-v3/app_secret_test.pem'))), OPENSSL_ALGO_SHA256);

        return new Response($status, [
            'alipay-timestamp' => $timestamp,
            'alipay-nonce' => $nonce,
            'alipay-signature' => base64_encode($sign),
            'alipay-sn' => CertManager::alipayGetAppCertSn(cert_path('alipay-v3/alipay_public_cert_test.crt')),
        ], $body);
    }
}
