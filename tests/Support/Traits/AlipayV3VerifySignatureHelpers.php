<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\CertManager;

/**
 * 支付宝 V3 响应验签 Helper。
 *
 * 生成带签名的模拟响应（签名密钥与测试租户支付宝公钥证书同属一对密钥）.
 */
trait AlipayV3VerifySignatureHelpers
{
    /**
     * @param null|string $alipaySn null 时省略 `alipay-sn` 头
     */
    private function makeSignedResponse(int $status, string $body, ?string $alipaySn = 'valid'): Response
    {
        $timestamp = (string) (int) (microtime(true) * 1000);
        $nonce = 'yansongda-nonce';
        $sign = $this->sign($timestamp."\n".$nonce."\n".$body."\n");

        $headers = ['alipay-timestamp' => $timestamp, 'alipay-nonce' => $nonce, 'alipay-signature' => base64_encode($sign)];

        if (null !== $alipaySn) {
            $headers['alipay-sn'] = 'valid' === $alipaySn
                ? CertManager::alipayGetAppCertSn(cert_path('alipay-v3/alipay_public_cert_test.crt'))
                : $alipaySn;
        }

        return new Response($status, $headers, $body);
    }

    private function sign(string $content): string
    {
        openssl_sign($content, $sign, openssl_pkey_get_private(file_get_contents(cert_path('alipay-v3/app_secret_test.pem'))), OPENSSL_ALGO_SHA256);

        return $sign;
    }
}
