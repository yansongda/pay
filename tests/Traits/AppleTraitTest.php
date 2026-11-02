<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use ReflectionMethod;
use RuntimeException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\AppleConfig;
use Yansongda\Pay\Crypto\Apple\Cryptor;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Tests\Support\Apple\AppleJwsFactory;
use Yansongda\Pay\Tests\Support\Apple\AppleTokenFactory;
use Yansongda\Pay\Traits\AppleTrait;
use Yansongda\Supports\Collection;

class AppleTraitStub
{
use AppleTrait;
}

const PAYMENT_DATA = [
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
uses(AppleTraitTestHelpers::class);

it('获取 Apple URL', function () {
    $normal = new AppleConfig(['mode' => PayMode::NORMAL]);
    $sandbox = new AppleConfig(['mode' => PayMode::SANDBOX]);
    $service = new AppleConfig(['mode' => PayMode::SERVICE]);

    expect(AppleTraitStub::getAppleUrl($normal, new Collection(['_url' => '/inApps/v1/subscriptions'])))->toBe('https://api.storekit.apple.com/inApps/v1/subscriptions');
    expect(AppleTraitStub::getAppleUrl($sandbox, new Collection(['_url' => '/inApps/v1/subscriptions'])))->toBe('https://api.storekit-sandbox.apple.com/inApps/v1/subscriptions');
    expect(AppleTraitStub::getAppleUrl($service, new Collection(['_service_url' => '/transactions/1000000047447934749'])))->toBe('https://api.storekit.apple.com/transactions/1000000047447934749');
    expect(AppleTraitStub::getAppleUrl($sandbox, new Collection(['_sandbox_url' => '/v1/sandbox', '_url' => '/v1/normal'])))->toBe('https://api.storekit-sandbox.apple.com/v1/sandbox');
    // merchantSession：http(s) 绝对地址直通，不按 mode 拼接
    expect(AppleTraitStub::getAppleUrl($normal, new Collection(['_url' => 'https://apple-pay-gateway.apple.com/paymentSession'])))->toBe('https://apple-pay-gateway.apple.com/paymentSession');
});

it('获取 Apple URL 缺少配置抛出异常', function () {
    $config = new AppleConfig(['mode' => PayMode::NORMAL]);

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_APPLE_URL_MISSING);

    AppleTraitStub::getAppleUrl($config, new Collection([]));
});

it('验证 Apple token', function () {
    $token = AppleTokenFactory::makeToken(PAYMENT_DATA);

    // array 形态
    $result = AppleTraitStub::verifyAppleToken($token, []);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('applicationPrimaryAccountNumber'))->toBe('378282246310005');
    expect($result->get('applicationExpirationDate'))->toBe('290101');
    expect($result->get('currencyCode'))->toBe('156');
    expect($result->get('transactionAmount'))->toBe(100);
    expect($result->get('paymentDataType'))->toBe('3DSecure');
    expect($result->get('_token_header')['transactionId'])->toBe($token['header']['transactionId']);

    // string（JSON）形态
    $resultFromJson = AppleTraitStub::verifyAppleToken((string) json_encode($token), []);
    expect($resultFromJson->get('transactionAmount'))->toBe(100);

    // Collection 形态
    $resultFromCollection = AppleTraitStub::verifyAppleToken(new Collection($token), []);
    expect($resultFromCollection->get('applicationPrimaryAccountNumber'))->toBe('378282246310005');
});

it('Apple token 携带 applicationData', function () {
    $applicationData = bin2hex(random_bytes(32));
    $token = AppleTokenFactory::makeToken(
        ['applicationPrimaryAccountNumber' => '378282246310005', 'transactionAmount' => 100],
        ['applicationData' => $applicationData]
    );

    $result = AppleTraitStub::verifyAppleToken($token, []);

    expect($result->get('transactionAmount'))->toBe(100);
    expect($result->get('_token_header')['applicationData'])->toBe($applicationData);
});

it('Apple token 数据被篡改', function () {
    $token = AppleTokenFactory::makeToken(PAYMENT_DATA, ['tamperData' => true]);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AppleTraitStub::verifyAppleToken($token, []);
});

it('Apple token 商户不匹配', function () {
    $token = AppleTokenFactory::makeToken(PAYMENT_DATA, [
        'merchantCert' => $this->certPath('untrusted-root.crt'),
    ]);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AppleTraitStub::verifyAppleToken($token, []);
});

it('Apple token 证书链不受信任', function () {
    $token = AppleTokenFactory::makeToken(PAYMENT_DATA, [
        'signerCert' => $this->certPath('untrusted-leaf.crt'),
        'signerKey' => $this->certPath('untrusted-leaf.key'),
        'intermediateCert' => $this->certPath('untrusted-intermediate.crt'),
    ]);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AppleTraitStub::verifyAppleToken($token, []);
});

it('Apple token 结构非法', function () {
    $token = AppleTokenFactory::makeToken(PAYMENT_DATA);

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
});

it('Apple token 签名时间过期', function () {
    $this->markTestSkipped(
        'signingTime 超时反路径不可测：fixture 由 openssl_pkcs7_sign 实时生成且不支持签名时间参数；'
        .'Trait 中缺失/超时均按 SIGN_ERROR 处理（真实 token 必含该属性，见 evidence）'
    );
});

it('Apple token 使用默认根证书', function () {
    $token = AppleTokenFactory::makeToken(PAYMENT_DATA);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AppleTraitStub::verifyAppleToken($token, ['_config' => 'no_root_ca']);
});

it('Apple token ASN.1 深度超限', function () {
    $token = AppleTokenFactory::makeToken(PAYMENT_DATA);

    // 70 层嵌套 SEQUENCE（超过 64 层上限），防恶意深嵌套 DER 栈溢出
    $der = "\x41";

    for ($i = 0; $i < 70; ++$i) {
        $len = strlen($der);
        $lenBytes = $len < 0x80 ? chr($len) : chr(0x81).chr($len);
        $der = "\x30".$lenBytes.$der;
    }

    $token['signature'] = base64_encode($der);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AppleTraitStub::verifyAppleToken($token, []);
});

it('Apple token 签名过长', function () {
    $token = AppleTokenFactory::makeToken(PAYMENT_DATA);

    // 超过 64KB 上限（真实 Apple token 约 3-4KB）
    $token['signature'] = base64_encode(str_repeat("\x30", 65537));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_APPLE_TOKEN_INVALID);

    AppleTraitStub::verifyAppleToken($token, []);
});

it('断言 Apple 证书未过期', function () {
    $method = new ReflectionMethod(Cryptor::class, 'assertCertNotExpired');

    // 有效期内：不抛异常
    $method->invoke(null, ['validFrom_time_t' => time() - 100, 'validTo_time_t' => time() + 100], 'leaf');

    // 已过期
    try {
        $method->invoke(null, ['validFrom_time_t' => time() - 200, 'validTo_time_t' => time() - 100], 'leaf');
        $this->fail('期望抛出 InvalidSignException');
    } catch (InvalidSignException $e) {
        expect($e->getCode())->toBe(Exception::SIGN_ERROR);
    }

    // 尚未生效
    try {
        $method->invoke(null, ['validFrom_time_t' => time() + 100, 'validTo_time_t' => time() + 200], 'leaf');
        $this->fail('期望抛出 InvalidSignException');
    } catch (InvalidSignException $e) {
        expect($e->getCode())->toBe(Exception::SIGN_ERROR);
    }
});

it('验证 Apple JWS', function () {
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

    expect($result['notificationType'])->toBe('EXPIRED');
    expect($result['data']['bundleId'])->toBe('com.yansongda.pay.test');
    expect($result['data'])->toHaveKey('signedTransactionInfo');
    expect($result['data']['signedTransactionInfo'])->toBeArray();
    expect($result['data']['signedTransactionInfo']['transactionId'])->toBe('1000000047447934749');
});

it('Apple JWS 被篡改', function () {
    $jws = AppleJwsFactory::makeJws(['notificationType' => 'SUBSCRIBED']);
    $parts = explode('.', $jws);

    $payload = (array) json_decode($this->b64UrlDecode($parts[1]), true);
    $payload['notificationType'] = 'TAMPERED';
    $tampered = $parts[0].'.'.$this->b64UrlEncode((string) json_encode($payload)).'.'.$parts[2];

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AppleTraitStub::verifyAppleJws($tampered, []);
});

it('Apple JWS 证书链过短', function () {
    $header = ['alg' => 'ES256', 'x5c' => [base64_encode($this->certDer('jws-leaf.crt'))]];
    $shortChain = $this->signWithJwsLeaf($header, ['notificationType' => 'SUBSCRIBED']);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AppleTraitStub::verifyAppleJws($shortChain, []);
});

it('Apple JWS 归属 bundle 不匹配', function () {
    $jws = AppleJwsFactory::makeJws([
        'notificationType' => 'SUBSCRIBED',
        'data' => [
            'bundleId' => 'com.evil.app',
            'environment' => 'Sandbox',
        ],
    ]);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AppleTraitStub::verifyAppleJws($jws, []);
});

it('Apple JWS 归属环境不匹配', function () {
    $jws = AppleJwsFactory::makeJws([
        'notificationType' => 'SUBSCRIBED',
        'data' => [
            'bundleId' => 'com.yansongda.pay.test',
            'environment' => 'Production',  // default 租户 mode 为 PayMode::SANDBOX
        ],
    ]);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AppleTraitStub::verifyAppleJws($jws, []);
});

it('未配置时 Apple JWS 归属校验跳过 bundle id', function () {
    $jws = AppleJwsFactory::makeJws([
        'notificationType' => 'SUBSCRIBED',
        'data' => [
            'bundleId' => 'com.any-other.app',
            'environment' => 'Sandbox',
        ],
    ]);

    $result = AppleTraitStub::verifyAppleJws($jws, ['_config' => 'no_api_key']);

    expect($result['notificationType'])->toBe('SUBSCRIBED');
});

it('Apple JWS 内层缺少 x5c 被拒绝', function () {
    $inner = $this->signWithJwsLeaf(['alg' => 'ES256'], ['transactionId' => '1000001234567890']);
    $jws = AppleJwsFactory::makeJws([
        'notificationType' => 'SUBSCRIBED',
        'data' => [
            'bundleId' => 'com.yansongda.pay.test',
            'environment' => 'Sandbox',
            'signedTransactionInfo' => $inner,
        ],
    ]);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AppleTraitStub::verifyAppleJws($jws, []);
});

it('生成 Apple JWT', function () {
    $jwt = AppleTraitStub::generateAppleJwt([]);
    $parts = explode('.', $jwt);

    expect($parts)->toHaveCount(3);

    $header = (array) json_decode($this->b64UrlDecode($parts[0]), true);
    $payload = (array) json_decode($this->b64UrlDecode($parts[1]), true);

    expect($header['alg'])->toBe('ES256');
    expect($header['kid'])->toBe('X5D4K9J2Q1'); // 与 tests/TestCase.php default 租户一致
    expect($header['typ'])->toBe('JWT');
    expect($payload['aud'])->toBe('appstoreconnect-v1');
    expect($payload['bid'])->toBe('com.yansongda.pay.test');
    expect($payload['iss'])->toBe('69a6de87-test-issuer-id');
    expect($payload['exp'] - $payload['iat'])->toBe(300);

    // 反向验证：raw 签名 → DER → openssl_verify（api.key fixture 公钥）
    $apiPrivateKey = openssl_pkey_get_private((string) file_get_contents($this->certPath('api.key')));
    expect($apiPrivateKey)->not->toBeFalse();

    $apiDetails = openssl_pkey_get_details($apiPrivateKey);
    expect($apiDetails)->not->toBeFalse();

    $apiPublicKey = openssl_pkey_get_public($apiDetails['key']);
    expect($apiPublicKey)->not->toBeFalse();

    expect(openssl_verify(
            $parts[0].'.'.$parts[1],
            $this->rawToDer($this->b64UrlDecode($parts[2])),
            $apiPublicKey,
            OPENSSL_ALGO_SHA256
        ))->toBe(1);
});

it('生成 Apple JWT 缺少 API 配置', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_APPLE_INVALID);

    AppleTraitStub::generateAppleJwt(['_config' => 'no_api_key']);
});

it('PKCS7 验签交叉校验', function () {
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
            [$this->certPath('token-root.crt')],
            $this->certPath('token-intermediate.crt'),
            $contentFile
        );

        expect((bool) $verified)->toBeTrue();
    } finally {
        @unlink($p7File);
        @unlink($contentFile);
        @unlink($outFile);
    }
});

it('PHP 8.5 以下不支持 RSA v1', function () {
    if (PHP_VERSION_ID >= 80500) {
        $this->markTestSkipped('PHP 8.5 容器无法覆盖 RSA_v1 的 PHP < 8.5 版本检查分支（见 evidence）');
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

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::DECRYPT_APPLE_FAILED);

    AppleTraitStub::verifyAppleToken($token, []);
});

it('验证 RSA token', function () {
    $token = AppleTokenFactory::makeRsaToken([
        'applicationPrimaryAccountNumber' => '378282246310005',
        'currencyCode' => '156',
        'transactionAmount' => 100,
    ]);

    // rsa 租户：payment_processing_cert = merchant-rsa.pem（PKCS#8 私钥 + 证书）
    $result = AppleTraitStub::verifyAppleToken($token, ['_config' => 'rsa']);

    expect($result->get('transactionAmount'))->toBe(100);
    expect($result->get('currencyCode'))->toBe('156');
    expect($result->get('_token_header')['transactionId'])->toBe($token['header']['transactionId']);
})->skip(!version_compare(PHP_VERSION, '8.5', '>='));

trait AppleTraitTestHelpers
{
    private function assertTokenInvalidParams(mixed $token): void
    {
        try {
            AppleTraitStub::verifyAppleToken($token, []);
            \PHPUnit\Framework\Assert::fail('期望抛出 InvalidParamsException');
        } catch (InvalidParamsException $e) {
            self::assertSame(Exception::PARAMS_APPLE_TOKEN_INVALID, $e->getCode());
        }
    }
    
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
