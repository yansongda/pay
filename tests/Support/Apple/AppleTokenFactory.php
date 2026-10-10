<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Apple;

use OpenSSLAsymmetricKey;
use RuntimeException;

/**
 * Apple 支付令牌测试 Factory。
 *
 * 运行时构造合法的 EC_v1 / RSA_v1 支付令牌（读取 tests/Cert/apple/ 下 fixture 证书），
 * 供 AppleTrait 验签解密单测使用。KDF 在测试侧独立实现，与 Trait 交叉验证。
 */
final class AppleTokenFactory
{
    private function __construct()
    {
    }

    /**
     * 构造 EC_v1 支付令牌。
     *
     * $opts 支持：
     * - merchantCert: 商户证书 PEM 路径（默认 merchant.pem，用于 pubkeyHash / ECDH）
     * - merchantId: 商户标识（默认与测试租户配置一致）
     * - applicationData: 附加应用数据（hex 字符串）
     * - tamperData: true 时篡改 data 密文（反路径：messageDigest 不一致）
     * - signerCert/signerKey/intermediateCert: 替换 PKCS#7 签名证书链（默认 token 链）
     *
     * @param array<string, mixed> $paymentData
     * @param array<string, mixed> $opts
     *
     * @return array{data: string, header: array<string, string>, signature: string, version: string}
     */
    public static function makeToken(array $paymentData, array $opts = []): array
    {
        $merchantCertPath = (string) ($opts['merchantCert'] ?? self::certPath('merchant.pem'));
        $merchantId = (string) ($opts['merchantId'] ?? 'merchant.com.yansongda.pay');

        $merchantPublicKey = self::certPublicKey($merchantCertPath);

        $ephemeralKey = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);

        if (false === $ephemeralKey) {
            throw new RuntimeException('生成临时 EC 密钥失败');
        }

        $sharedSecret = openssl_pkey_derive($merchantPublicKey, $ephemeralKey);

        if (false === $sharedSecret) {
            throw new RuntimeException('ECDH 密钥协商失败');
        }

        $symmetricKey = self::deriveSymmetricKey($sharedSecret, $merchantId);

        $iv = str_repeat("\x00", 16);
        $tag = '';
        $cipherText = openssl_encrypt((string) json_encode($paymentData), 'aes-256-gcm', $symmetricKey, OPENSSL_RAW_DATA, $iv, $tag);

        if (false === $cipherText) {
            throw new RuntimeException('AES-256-GCM 加密失败');
        }

        $merchantDetails = openssl_pkey_get_details($merchantPublicKey);
        $ephemeralDetails = openssl_pkey_get_details($ephemeralKey);

        if (false === $merchantDetails || false === $ephemeralDetails || !isset($merchantDetails['key'], $ephemeralDetails['key'])) {
            throw new RuntimeException('读取公钥细节失败');
        }

        $merchantSpkiDer = self::pemBlockToDer((string) $merchantDetails['key']);
        $ephemeralSpkiDer = self::pemBlockToDer((string) $ephemeralDetails['key']);

        $transactionId = bin2hex(random_bytes(16));
        $header = [
            'ephemeralPublicKey' => base64_encode($ephemeralSpkiDer),
            'publicKeyHash' => base64_encode(hash('sha256', $merchantSpkiDer, true)),
            'transactionId' => $transactionId,
        ];

        // 签名内容：ephemeralPublicKey || data(cipherText+tag) || transactionId [|| applicationData]
        $signedData = $ephemeralSpkiDer.$cipherText.$tag.hex2bin($transactionId);

        if (isset($opts['applicationData']) && '' !== (string) $opts['applicationData']) {
            $signedData .= hex2bin((string) $opts['applicationData']);
            $header['applicationData'] = (string) $opts['applicationData'];
        }

        $signatureDer = self::pkcs7SignAndExtractDer(
            $signedData,
            (string) ($opts['signerCert'] ?? self::certPath('token-leaf.crt')),
            (string) ($opts['signerKey'] ?? self::certPath('token-leaf.key')),
            (string) ($opts['intermediateCert'] ?? self::certPath('token-intermediate.crt'))
        );

        $dataBinary = $cipherText.$tag;

        if (!empty($opts['tamperData'])) {
            // 反路径：签名在原密文上进行，data 字段随后被篡改 → messageDigest 校验失败（SIGN_ERROR）
            $dataBinary = substr($dataBinary, 0, -1).(substr($dataBinary, -1) ^ "\x01");
        }

        return [
            'data' => base64_encode($dataBinary),
            'header' => $header,
            'signature' => base64_encode($signatureDer),
            'version' => 'EC_v1',
        ];
    }

    /**
     * 构造 RSA_v1 支付令牌（AES-128-GCM + RSA-OAEP-SHA256，需 PHP >= 8.5）。
     *
     * @param array<string, mixed> $paymentData
     * @param array<string, mixed> $opts
     *
     * @return array{data: string, header: array<string, string>, signature: string, version: string}
     */
    public static function makeRsaToken(array $paymentData, array $opts = []): array
    {
        $merchantCertPath = (string) ($opts['merchantCert'] ?? self::certPath('merchant-rsa.pem'));
        $merchantPublicKey = self::certPublicKey($merchantCertPath);

        $symmetricKey = random_bytes(16);
        $wrappedKey = '';

        // PHP 8.5+：openssl_public_encrypt 的 digest_algo 为第 5 位置参数（OAEP SHA-256）
        if (!openssl_public_encrypt($symmetricKey, $wrappedKey, $merchantPublicKey, OPENSSL_PKCS1_OAEP_PADDING, 'sha256')) {
            throw new RuntimeException('RSA-OAEP-SHA256 加密失败: '.openssl_error_string());
        }

        $iv = str_repeat("\x00", 16);
        $tag = '';
        $cipherText = openssl_encrypt((string) json_encode($paymentData), 'aes-128-gcm', $symmetricKey, OPENSSL_RAW_DATA, $iv, $tag);

        if (false === $cipherText) {
            throw new RuntimeException('AES-128-GCM 加密失败');
        }

        $merchantDetails = openssl_pkey_get_details($merchantPublicKey);

        if (false === $merchantDetails || !isset($merchantDetails['key'])) {
            throw new RuntimeException('读取公钥细节失败');
        }

        $merchantSpkiDer = self::pemBlockToDer((string) $merchantDetails['key']);

        $transactionId = bin2hex(random_bytes(16));
        $header = [
            'wrappedKey' => base64_encode($wrappedKey),
            'publicKeyHash' => base64_encode(hash('sha256', $merchantSpkiDer, true)),
            'transactionId' => $transactionId,
        ];

        // 签名内容：wrappedKey || data(cipherText+tag) || transactionId
        $signedData = $wrappedKey.$cipherText.$tag.hex2bin($transactionId);

        $signatureDer = self::pkcs7SignAndExtractDer(
            $signedData,
            (string) ($opts['signerCert'] ?? self::certPath('token-leaf.crt')),
            (string) ($opts['signerKey'] ?? self::certPath('token-leaf.key')),
            (string) ($opts['intermediateCert'] ?? self::certPath('token-intermediate.crt'))
        );

        return [
            'data' => base64_encode($cipherText.$tag),
            'header' => $header,
            'signature' => base64_encode($signatureDer),
            'version' => 'RSA_v1',
        ];
    }

    /**
     * 对二进制内容做 detached PKCS#7 签名，经 S/MIME → DER 后处理返回纯 DER。
     */
    private static function pkcs7SignAndExtractDer(string $content, string $signerCertPath, string $signerKeyPath, string $intermediateCertPath): string
    {
        $contentFile = (string) tempnam(sys_get_temp_dir(), 'apple-token-content-');
        $outFile = (string) tempnam(sys_get_temp_dir(), 'apple-token-p7s-');

        try {
            file_put_contents($contentFile, $content);

            if (!openssl_pkcs7_sign(
                $contentFile,
                $outFile,
                'file://'.$signerCertPath,
                ['file://'.$signerKeyPath, null],
                [],
                PKCS7_DETACHED | PKCS7_BINARY,
                $intermediateCertPath
            )) {
                throw new RuntimeException('openssl_pkcs7_sign 失败: '.openssl_error_string());
            }

            return self::smimeToDer((string) file_get_contents($outFile));
        } finally {
            unlink($contentFile);
            unlink($outFile);
        }
    }

    /**
     * 提取 S/MIME 输出中的 PKCS#7 纯 DER（三种输出形态容错）。
     *
     * openssl_pkcs7_sign(headers=[]) 实际输出 MIME multipart/signed 消息
     * （第二段为 p7s base64）；兼容纯 base64 body 与 PEM 包装形态。
     */
    private static function smimeToDer(string $smime): string
    {
        // 1. 整体 strict base64（无 MIME 头的裸 base64 body）
        $compact = (string) preg_replace('/\s+/', '', $smime);
        $der = base64_decode($compact, true);

        if (false !== $der && '' !== $der) {
            return $der;
        }

        // 2. PEM 包装（-----BEGIN ... / -----END ... 之间）
        $begin = strpos($smime, '-----BEGIN');
        $end = strpos($smime, '-----END');

        if (false !== $begin && false !== $end) {
            $end = strpos($smime, '-----', $end + 5);

            if (false !== $end) {
                $der = base64_decode((string) preg_replace('/\s+/', '', substr($smime, $begin + 11, $end - $begin - 11)), true);

                if (false !== $der && '' !== $der) {
                    return $der;
                }
            }
        }

        // 3. MIME multipart："smime.p7s" 附件段 → 空行后的 base64（到边界行止）
        $pos = strrpos($smime, 'Content-Transfer-Encoding: base64');

        if (false === $pos) {
            throw new RuntimeException('解析 PKCS#7 S/MIME 输出失败：找不到 base64 段');
        }

        $tail = substr($smime, $pos + strlen('Content-Transfer-Encoding: base64'));
        $blankLine = strpos($tail, "\n\n");
        $body = false === $blankLine ? $tail : substr($tail, $blankLine + 2);
        $lines = preg_split('/\r?\n/', $body);

        if (false === $lines) {
            throw new RuntimeException('解析 PKCS#7 S/MIME 输出失败');
        }

        $base64 = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (str_starts_with($trimmed, '--')) {
                break;
            }

            $base64 .= $trimmed;
        }

        $der = base64_decode($base64, true);

        if (false === $der || '' === $der) {
            throw new RuntimeException('PKCS#7 S/MIME → DER 解码失败');
        }

        return $der;
    }

    /**
     * ECDH 共享密钥 → AES-256-GCM 对称密钥（NIST SP 800-56A 计数器模式 KDF，测试侧独立实现）。
     */
    private static function deriveSymmetricKey(string $sharedSecret, string $merchantId): string
    {
        return hash(
            'sha256',
            "\x00\x00\x00\x01".$sharedSecret.chr(0x0D).'id-aes256-GCM'.'Apple'.hash('sha256', trim($merchantId), true),
            true
        );
    }

    private static function certPublicKey(string $certPath): OpenSSLAsymmetricKey
    {
        $cert = openssl_x509_read((string) file_get_contents($certPath));

        if (false === $cert) {
            throw new RuntimeException('读取证书失败: '.$certPath);
        }

        $publicKey = openssl_pkey_get_public($cert);

        if (false === $publicKey) {
            throw new RuntimeException('读取证书公钥失败: '.$certPath);
        }

        return $publicKey;
    }

    private static function pemBlockToDer(string $pem): string
    {
        $lines = preg_split('/\r?\n/', $pem);

        if (false === $lines) {
            throw new RuntimeException('解析 PEM 失败');
        }

        $base64 = '';
        $inBlock = false;

        foreach ($lines as $line) {
            if (str_starts_with($line, '-----BEGIN')) {
                $inBlock = true;

                continue;
            }

            if (str_starts_with($line, '-----END')) {
                break;
            }

            if ($inBlock) {
                $base64 .= trim($line);
            }
        }

        $der = base64_decode($base64, true);

        if (false === $der) {
            throw new RuntimeException('PEM 解码失败');
        }

        return $der;
    }

    private static function certPath(string $name): string
    {
        return dirname(__DIR__, 2).'/Cert/apple/'.$name;
    }
}