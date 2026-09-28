<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use PHPUnit\Framework\Attributes\RequiresPhp;
use ReflectionMethod;
use RuntimeException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\AppleConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\Support\Apple\AppleJwsFactory;
use Yansongda\Pay\Tests\Support\Apple\AppleTokenFactory;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\AppleTrait;
use Yansongda\Supports\Collection;

class AppleTraitStub
{
    use AppleTrait;
}

class AppleTraitTest extends TestCase
{
    private const PAYMENT_DATA = [
        'applicationPrimaryAccountNumber' => '378282246310005',
        'applicationExpirationDate' => '290101',
        'currencyCode' => '156',
        'transactionAmount' => 100,
        'cardholderName' => 'Yansongda Pay',
        'deviceManufacturerIdentifier' => '050103030300',
        'paymentDataType' => '3DSecure',
        'paymentData' => [
            'onlinePaymentCryptogram' => 'AgAAAAAACcm3BAYGAgIIAw==',
            'eciIndicator' => '05',
        ],
    ];

    public function testGetAppleUrl(): void
    {
        $normal = new AppleConfig(['mode' => Pay::MODE_NORMAL]);
        $sandbox = new AppleConfig(['mode' => Pay::MODE_SANDBOX]);
        $service = new AppleConfig(['mode' => Pay::MODE_SERVICE]);

        self::assertSame(
            'https://api.storekit.apple.com/inApps/v1/subscriptions',
            AppleTraitStub::getAppleUrl($normal, new Collection(['_url' => '/inApps/v1/subscriptions']))
        );
        self::assertSame(
            'https://api.storekit-sandbox.apple.com/inApps/v1/subscriptions',
            AppleTraitStub::getAppleUrl($sandbox, new Collection(['_url' => '/inApps/v1/subscriptions']))
        );
        self::assertSame(
            'https://api.storekit.apple.com/transactions/1000000047447934749',
            AppleTraitStub::getAppleUrl($service, new Collection(['_service_url' => '/transactions/1000000047447934749']))
        );
        self::assertSame(
            'https://api.storekit-sandbox.apple.com/v1/sandbox',
            AppleTraitStub::getAppleUrl($sandbox, new Collection(['_sandbox_url' => '/v1/sandbox', '_url' => '/v1/normal']))
        );
        // merchantSession：http(s) 绝对地址直通，不按 mode 拼接
        self::assertSame(
            'https://apple-pay-gateway.apple.com/paymentSession',
            AppleTraitStub::getAppleUrl($normal, new Collection(['_url' => 'https://apple-pay-gateway.apple.com/paymentSession']))
        );
    }

    public function testGetAppleUrlMissingThrowsException(): void
    {
        $config = new AppleConfig(['mode' => Pay::MODE_NORMAL]);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_APPLE_URL_MISSING);

        AppleTraitStub::getAppleUrl($config, new Collection([]));
    }

    public function testVerifyAppleToken(): void
    {
        $token = AppleTokenFactory::makeToken(self::PAYMENT_DATA);

        // array 形态
        $result = AppleTraitStub::verifyAppleToken($token, []);

        self::assertInstanceOf(Collection::class, $result);
        self::assertSame('378282246310005', $result->get('applicationPrimaryAccountNumber'));
        self::assertSame('290101', $result->get('applicationExpirationDate'));
        self::assertSame('156', $result->get('currencyCode'));
        self::assertSame(100, $result->get('transactionAmount'));
        self::assertSame('3DSecure', $result->get('paymentDataType'));
        self::assertSame($token['header']['transactionId'], $result->get('_token_header')['transactionId']);

        // string（JSON）形态
        $resultFromJson = AppleTraitStub::verifyAppleToken((string) json_encode($token), []);
        self::assertSame(100, $resultFromJson->get('transactionAmount'));

        // Collection 形态
        $resultFromCollection = AppleTraitStub::verifyAppleToken(new Collection($token), []);
        self::assertSame('378282246310005', $resultFromCollection->get('applicationPrimaryAccountNumber'));
    }

    public function testVerifyAppleTokenWithApplicationData(): void
    {
        $applicationData = bin2hex(random_bytes(32));
        $token = AppleTokenFactory::makeToken(
            ['applicationPrimaryAccountNumber' => '378282246310005', 'transactionAmount' => 100],
            ['applicationData' => $applicationData]
        );

        $result = AppleTraitStub::verifyAppleToken($token, []);

        self::assertSame(100, $result->get('transactionAmount'));
        self::assertSame($applicationData, $result->get('_token_header')['applicationData']);
    }

    public function testVerifyAppleTokenTamperedData(): void
    {
        $token = AppleTokenFactory::makeToken(self::PAYMENT_DATA, ['tamperData' => true]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AppleTraitStub::verifyAppleToken($token, []);
    }

    public function testVerifyAppleTokenWrongMerchant(): void
    {
        // 用非配置商户证书（untrusted-root）参与 KDF/pubkeyHash → 商户证书哈希不匹配
        $token = AppleTokenFactory::makeToken(self::PAYMENT_DATA, [
            'merchantCert' => self::certPath('untrusted-root.crt'),
        ]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AppleTraitStub::verifyAppleToken($token, []);
    }

    public function testVerifyAppleTokenUntrustedChain(): void
    {
        // untrusted 链签名（leaf/intermediate 自洽，但 intermediate 不由 token-root 签发）
        $token = AppleTokenFactory::makeToken(self::PAYMENT_DATA, [
            'signerCert' => self::certPath('untrusted-leaf.crt'),
            'signerKey' => self::certPath('untrusted-leaf.key'),
            'intermediateCert' => self::certPath('untrusted-intermediate.crt'),
        ]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AppleTraitStub::verifyAppleToken($token, []);
    }

    public function testVerifyAppleTokenInvalidStructure(): void
    {
        $token = AppleTokenFactory::makeToken(self::PAYMENT_DATA);

        // 缺 data
        $missingData = $token;
        unset($missingData['data']);

        // transactionId 非 hex
        $badHexTransactionId = $token;
        $badHexTransactionId['header']['transactionId'] = 'zz-not-hex';

        // 非法 applicationData（非 hex）
        $badAppData = $token;
        $badAppData['header']['applicationData'] = 'not-hex!!';

        $this->assertTokenInvalidParams($missingData);
        $this->assertTokenInvalidParams($badHexTransactionId);
        $this->assertTokenInvalidParams($badAppData);
        $this->assertTokenInvalidParams('this is not json');
        $this->assertTokenInvalidParams(new Collection(['data' => 'x', 'signature' => 'x', 'version' => 'EC_v1']));
    }

    public function testVerifyAppleTokenSigningTimeExpired(): void
    {
        self::markTestSkipped(
            'signingTime 超时反路径不可测：fixture 由 openssl_pkcs7_sign 实时生成且不支持签名时间参数；'
            .'Trait 中缺失/超时均按 SIGN_ERROR 处理（真实 token 必含该属性，见 evidence）'
        );
    }

    public function testVerifyAppleTokenWithDefaultRootCa(): void
    {
        // 未配置 apple_root_ca：默认使用内置 AppleRootCA-G3.pem 参与链验证，
        // fixture 的 token-root 不在内置信任链内 → 验签失败（而非配置缺失异常）
        $token = AppleTokenFactory::makeToken(self::PAYMENT_DATA);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AppleTraitStub::verifyAppleToken($token, ['_config' => 'no_root_ca']);
    }

    public function testVerifyAppleTokenAsn1DepthExceeded(): void
    {
        $token = AppleTokenFactory::makeToken(self::PAYMENT_DATA);

        // 70 层嵌套 SEQUENCE（超过 64 层上限），防恶意深嵌套 DER 栈溢出
        $der = "\x41";

        for ($i = 0; $i < 70; ++$i) {
            $len = strlen($der);
            $lenBytes = $len < 0x80 ? chr($len) : chr(0x81).chr($len);
            $der = "\x30".$lenBytes.$der;
        }

        $token['signature'] = base64_encode($der);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AppleTraitStub::verifyAppleToken($token, []);
    }

    public function testVerifyAppleTokenSignatureTooLong(): void
    {
        $token = AppleTokenFactory::makeToken(self::PAYMENT_DATA);

        // 超过 64KB 上限（真实 Apple token 约 3-4KB）
        $token['signature'] = base64_encode(str_repeat("\x30", 65537));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_APPLE_TOKEN_INVALID);

        AppleTraitStub::verifyAppleToken($token, []);
    }

    public function testAssertAppleCertNotExpired(): void
    {
        $method = new ReflectionMethod(AppleTraitStub::class, 'assertAppleCertNotExpired');

        // 有效期内：不抛异常
        $method->invoke(null, ['validFrom_time_t' => time() - 100, 'validTo_time_t' => time() + 100], 'leaf');

        // 已过期
        try {
            $method->invoke(null, ['validFrom_time_t' => time() - 200, 'validTo_time_t' => time() - 100], 'leaf');
            self::fail('期望抛出 InvalidSignException');
        } catch (InvalidSignException $e) {
            self::assertSame(Exception::SIGN_ERROR, $e->getCode());
        }

        // 尚未生效
        try {
            $method->invoke(null, ['validFrom_time_t' => time() + 100, 'validTo_time_t' => time() + 200], 'leaf');
            self::fail('期望抛出 InvalidSignException');
        } catch (InvalidSignException $e) {
            self::assertSame(Exception::SIGN_ERROR, $e->getCode());
        }
    }

    public function testVerifyAppleJws(): void
    {
        $signedTransactionInfo = AppleJwsFactory::makeJws([
            'transactionId' => '1000000047447934749',
            'originalTransactionId' => '1000000047447934749',
            'productId' => 'com.yansongda.pay.test',
            'type' => 'Consumable',
        ]);

        $jws = AppleJwsFactory::makeJws([
            'notificationType' => 'EXPIRED',
            'subtype' => 'VOLUNTARY',
            'data' => [
                'appAppleId' => 1234567890,
                'bundleId' => 'com.yansongda.pay.test',
                'environment' => 'Sandbox',
                'signedTransactionInfo' => $signedTransactionInfo,
            ],
            'notificationUUID' => 'f24a8c63-33fb-4316-87e8-79c0460df101',
        ]);

        $result = AppleTraitStub::verifyAppleJws($jws, []);

        self::assertSame('EXPIRED', $result['notificationType']);
        self::assertSame('com.yansongda.pay.test', $result['data']['bundleId']);
        self::assertArrayHasKey('signedTransactionInfo', $result['data']);
        self::assertIsArray($result['data']['signedTransactionInfo']);
        self::assertSame('1000000047447934749', $result['data']['signedTransactionInfo']['transactionId']);
    }

    public function testVerifyAppleJwsTampered(): void
    {
        $jws = AppleJwsFactory::makeJws(['notificationType' => 'SUBSCRIBED']);
        $parts = explode('.', $jws);

        $payload = (array) json_decode(self::b64UrlDecode($parts[1]), true);
        $payload['notificationType'] = 'TAMPERED';
        $tampered = $parts[0].'.'.self::b64UrlEncode((string) json_encode($payload)).'.'.$parts[2];

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AppleTraitStub::verifyAppleJws($tampered, []);
    }

    public function testVerifyAppleJwsShortChain(): void
    {
        // x5c 仅 1 元素（leaf），链不完整
        $header = ['alg' => 'ES256', 'x5c' => [base64_encode(self::certDer('jws-leaf.crt'))]];
        $shortChain = self::signWithJwsLeaf($header, ['notificationType' => 'SUBSCRIBED']);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AppleTraitStub::verifyAppleJws($shortChain, []);
    }

    public function testVerifyAppleJwsOwnershipBundleMismatch(): void
    {
        // 归属校验：真实签名但 bundleId 与配置不匹配（防跨 App 伪造）
        $jws = AppleJwsFactory::makeJws([
            'notificationType' => 'SUBSCRIBED',
            'data' => [
                'bundleId' => 'com.evil.app',
                'environment' => 'Sandbox',
            ],
        ]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AppleTraitStub::verifyAppleJws($jws, []);
    }

    public function testVerifyAppleJwsOwnershipEnvironmentMismatch(): void
    {
        // 归属校验：environment 与配置模式不匹配（防沙盒/生产串扰）
        $jws = AppleJwsFactory::makeJws([
            'notificationType' => 'SUBSCRIBED',
            'data' => [
                'bundleId' => 'com.yansongda.pay.test',
                'environment' => 'Production',  // default 租户 mode 为 MODE_SANDBOX
            ],
        ]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AppleTraitStub::verifyAppleJws($jws, []);
    }

    public function testVerifyAppleJwsOwnershipSkipsBundleIdWithoutConfig(): void
    {
        // no_api_key 租户未配置 bundle_id：跳过 bundleId 校验，environment 仍须匹配
        $jws = AppleJwsFactory::makeJws([
            'notificationType' => 'SUBSCRIBED',
            'data' => [
                'bundleId' => 'com.any-other.app',
                'environment' => 'Sandbox',
            ],
        ]);

        $result = AppleTraitStub::verifyAppleJws($jws, ['_config' => 'no_api_key']);

        self::assertSame('SUBSCRIBED', $result['notificationType']);
    }

    public function testVerifyAppleJwsInnerJwsWithoutX5cRejected(): void
    {
        // 对齐官方库：二级 JWS 也须自带 x5c，无 x5c 一律拒绝（原「回退已信任 leaf 公钥」已移除）
        $inner = self::signWithJwsLeaf(['alg' => 'ES256'], ['transactionId' => '1000001234567890']);
        $jws = AppleJwsFactory::makeJws([
            'notificationType' => 'SUBSCRIBED',
            'data' => [
                'bundleId' => 'com.yansongda.pay.test',
                'environment' => 'Sandbox',
                'signedTransactionInfo' => $inner,
            ],
        ]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AppleTraitStub::verifyAppleJws($jws, []);
    }

    public function testGenerateAppleJwt(): void
    {
        $jwt = AppleTraitStub::generateAppleJwt([]);
        $parts = explode('.', $jwt);

        self::assertCount(3, $parts);

        $header = (array) json_decode(self::b64UrlDecode($parts[0]), true);
        $payload = (array) json_decode(self::b64UrlDecode($parts[1]), true);

        self::assertSame('ES256', $header['alg']);
        self::assertSame('X5D4K9J2Q1', $header['kid']); // 与 tests/TestCase.php default 租户一致
        self::assertSame('JWT', $header['typ']);
        self::assertSame('appstoreconnect-v1', $payload['aud']);
        self::assertSame('com.yansongda.pay.test', $payload['bid']);
        self::assertSame('69a6de87-test-issuer-id', $payload['iss']);
        self::assertSame(300, $payload['exp'] - $payload['iat']);

        // 反向验证：raw 签名 → DER → openssl_verify（api.key fixture 公钥）
        $apiPrivateKey = openssl_pkey_get_private((string) file_get_contents(self::certPath('api.key')));
        self::assertNotFalse($apiPrivateKey);

        $apiDetails = openssl_pkey_get_details($apiPrivateKey);
        self::assertNotFalse($apiDetails);

        $apiPublicKey = openssl_pkey_get_public($apiDetails['key']);
        self::assertNotFalse($apiPublicKey);

        self::assertSame(
            1,
            openssl_verify(
                $parts[0].'.'.$parts[1],
                self::rawToDer(self::b64UrlDecode($parts[2])),
                $apiPublicKey,
                OPENSSL_ALGO_SHA256
            )
        );
    }

    public function testGenerateAppleJwtMissingApiConfig(): void
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_APPLE_INVALID);

        AppleTraitStub::generateAppleJwt(['_config' => 'no_api_key']);
    }

    public function testPkcs7VerifyCrossCheck(): void
    {
        // Fetch token 后重拼签名内容，用 openssl_pkcs7_verify 对拍（双方实现一致性）
        $token = AppleTokenFactory::makeToken(['transactionAmount' => 100, 'currencyCode' => '156']);
        $header = $token['header'];

        $content = base64_decode($header['ephemeralPublicKey'], true)
            .base64_decode($token['data'], true)
            .hex2bin($header['transactionId']);

        $p7File = (string) tempnam(sys_get_temp_dir(), 'apple-p7-');
        $contentFile = (string) tempnam(sys_get_temp_dir(), 'apple-p7c-');
        $outFile = (string) tempnam(sys_get_temp_dir(), 'apple-p7o-');

        file_put_contents($p7File, (string) base64_decode($token['signature'], true));
        file_put_contents($contentFile, $content);

        try {
            $verified = openssl_pkcs7_verify(
                $p7File,
                PKCS7_DETACHED | PKCS7_BINARY,
                $outFile,
                [self::certPath('token-root.crt')],
                self::certPath('token-intermediate.crt'),
                $contentFile
            );

            self::assertTrue((bool) $verified);
        } finally {
            @unlink($p7File);
            @unlink($contentFile);
            @unlink($outFile);
        }
    }

    public function testRsaV1UnsupportedOnPhpBelow85(): void
    {
        if (PHP_VERSION_ID >= 80500) {
            self::markTestSkipped('PHP 8.5 容器无法覆盖 RSA_v1 的 PHP < 8.5 版本检查分支（见 evidence）');
        }

        // PHP < 8.5 环境：openssl_private_decrypt 无 digest_algo 参数，Trait 应在入口（版本解析）拒绝，
        // 早于结构/签名/证书校验；故此处只需结构合法的最小令牌（字段非 base64 会被前置校验提前拦下）
        $token = [
            'data' => base64_encode(str_repeat('A', 32)),
            'header' => [
                'wrappedKey' => base64_encode(str_repeat('B', 256)),
                'publicKeyHash' => base64_encode(str_repeat('C', 32)),
                'transactionId' => '00',
            ],
            'signature' => base64_encode(str_repeat('D', 64)),
            'version' => 'RSA_v1',
        ];

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::DECRYPT_APPLE_FAILED);

        AppleTraitStub::verifyAppleToken($token, []);
    }

    #[RequiresPhp('>= 8.5')]
    public function testVerifyRsaToken(): void
    {
        $token = AppleTokenFactory::makeRsaToken([
            'applicationPrimaryAccountNumber' => '378282246310005',
            'currencyCode' => '156',
            'transactionAmount' => 100,
        ]);

        // rsa 租户：payment_processing_cert = merchant-rsa.pem（PKCS#8 私钥 + 证书）
        $result = AppleTraitStub::verifyAppleToken($token, ['_config' => 'rsa']);

        self::assertSame(100, $result->get('transactionAmount'));
        self::assertSame('156', $result->get('currencyCode'));
        self::assertSame($token['header']['transactionId'], $result->get('_token_header')['transactionId']);
    }

    private function assertTokenInvalidParams(mixed $token): void
    {
        try {
            AppleTraitStub::verifyAppleToken($token, []);
            self::fail('期望抛出 InvalidParamsException');
        } catch (InvalidParamsException $e) {
            self::assertSame(Exception::PARAMS_APPLE_TOKEN_INVALID, $e->getCode());
        }
    }

    /**
     * 测试内独立实现：用 jws-leaf.key 对任意 header/payload 签名（反路径专用）。
     *
     * @param array<string, mixed> $header
     * @param array<string, mixed> $payload
     */
    private static function signWithJwsLeaf(array $header, array $payload): string
    {
        $headerB64 = self::b64UrlEncode((string) json_encode($header));
        $payloadB64 = self::b64UrlEncode((string) json_encode($payload));

        $privateKey = openssl_pkey_get_private((string) file_get_contents(self::certPath('jws-leaf.key')));

        if (false === $privateKey) {
            throw new RuntimeException('读取 jws-leaf 私钥失败');
        }

        $derSignature = '';

        if (!openssl_sign($headerB64.'.'.$payloadB64, $derSignature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('ES256 签名失败');
        }

        return $headerB64.'.'.$payloadB64.'.'.self::b64UrlEncode(self::derToRaw($derSignature));
    }

    /**
     * 测试内独立实现：ECDSA DER → raw（r || s，各 32 字节）。
     */
    private static function derToRaw(string $der): string
    {
        $offset = 0;

        if ("\x30" !== ($der[$offset++] ?? '')) {
            throw new RuntimeException('ECDSA DER 签名结构非法');
        }

        $sequenceLength = self::readDerLength($der, $offset);
        $sequenceEnd = $offset + $sequenceLength;
        $r = self::readDerInteger($der, $offset);
        $s = self::readDerInteger($der, $offset);

        if ($sequenceEnd !== $offset) {
            throw new RuntimeException('ECDSA DER 签名结构非法');
        }

        return str_pad($r, 32, "\x00", STR_PAD_LEFT).str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }

    private static function readDerInteger(string $der, int &$offset): string
    {
        if ("\x02" !== ($der[$offset++] ?? '')) {
            throw new RuntimeException('ECDSA DER INTEGER 非法');
        }

        $length = self::readDerLength($der, $offset);
        $value = substr($der, $offset, $length);
        $offset += $length;

        // 剥前导 0x00（符号位填充，仅当下一字节高位为 1）
        if (1 < strlen($value) && "\x00" === $value[0] && 0 !== (ord($value[1]) & 0x80)) {
            $value = substr($value, 1);
        }

        return $value;
    }

    private static function readDerLength(string $der, int &$offset): int
    {
        $length = ord($der[$offset++] ?? '');

        if (0 !== ($length & 0x80)) {
            $count = $length & 0x7F;
            $length = 0;

            for ($i = 0; $i < $count; ++$i) {
                $length = ($length << 8) | ord($der[$offset++] ?? '');
            }
        }

        return $length;
    }

    /**
     * 测试内独立实现：raw（r || s，各 32 字节）→ ECDSA DER。
     */
    private static function rawToDer(string $raw): string
    {
        $r = self::padInteger(substr($raw, 0, 32));
        $s = self::padInteger(substr($raw, 32, 32));

        $sequence = "\x02".chr(strlen($r)).$r."\x02".chr(strlen($s)).$s;

        return "\x30".chr(strlen($sequence)).$sequence;
    }

    private static function padInteger(string $value): string
    {
        $value = ltrim($value, "\x00");

        if ('' === $value) {
            $value = "\x00";
        }

        // 最高位为 1 时补 0x00 防符号位
        if (0 !== (ord($value[0]) & 0x80)) {
            $value = "\x00".$value;
        }

        return $value;
    }

    private static function b64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64UrlDecode(string $data): string
    {
        $padding = (4 - strlen($data) % 4) % 4;

        return (string) base64_decode(strtr($data, '-_', '+/').str_repeat('=', $padding), true);
    }

    private static function certDer(string $name): string
    {
        $pem = (string) file_get_contents(self::certPath($name));
        $lines = preg_split('/\r?\n/', $pem);

        if (false === $lines) {
            throw new RuntimeException('解析证书 PEM 失败');
        }

        $base64 = '';
        $inBlock = false;

        foreach ($lines as $line) {
            if ('-----BEGIN CERTIFICATE-----' === $line) {
                $inBlock = true;

                continue;
            }

            if ('-----END CERTIFICATE-----' === $line) {
                break;
            }

            if ($inBlock) {
                $base64 .= trim($line);
            }
        }

        return (string) base64_decode($base64, true);
    }

    private static function certPath(string $name): string
    {
        return dirname(__DIR__).'/Cert/apple/'.$name;
    }
}