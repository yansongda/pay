<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\AppleConfig;
use Yansongda\Pay\Crypto\Apple\Cryptor;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Tests\Support\Apple\AppleJwsFactory;
use Yansongda\Pay\Tests\Support\Apple\AppleTokenFactory;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Support\Traits\AppleTraitHelpers;
use Yansongda\Pay\Tests\Stubs\AppleTraitStub;
use Yansongda\Pay\Tests\Support\Apple\PaymentData;

uses(AppleTraitHelpers::class);

describe('getAppleUrl', function () {
    it('正常', function () {
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

    it('缺少配置抛出异常', function () {
        $config = new AppleConfig(['mode' => PayMode::NORMAL]);

        AppleTraitStub::getAppleUrl($config, new Collection([]));
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_APPLE_URL_MISSING);
});

describe('verifyAppleToken', function () {
    it('正常', function () {
        $token = AppleTokenFactory::makeToken(PaymentData::PAYMENT_DATA);

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

    it('携带 applicationData', function () {
        $applicationData = bin2hex(random_bytes(32));
        $token = AppleTokenFactory::makeToken(
            ['applicationPrimaryAccountNumber' => '378282246310005', 'transactionAmount' => 100],
            ['applicationData' => $applicationData]
        );

        $result = AppleTraitStub::verifyAppleToken($token, []);

        expect($result->get('transactionAmount'))->toBe(100);
        expect($result->get('_token_header')['applicationData'])->toBe($applicationData);
    });

    it('数据被篡改', function () {
        $token = AppleTokenFactory::makeToken(PaymentData::PAYMENT_DATA, ['tamperData' => true]);

        AppleTraitStub::verifyAppleToken($token, []);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

    it('商户不匹配', function () {
        // 用非配置商户证书（untrusted-root）参与 KDF/pubkeyHash → 商户证书哈希不匹配
        $token = AppleTokenFactory::makeToken(PaymentData::PAYMENT_DATA, [
            'merchantCert' => $this->certPath('untrusted-root.crt'),
        ]);

        AppleTraitStub::verifyAppleToken($token, []);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

    it('证书链不受信任', function () {
        // untrusted 链签名（leaf/intermediate 自洽，但 intermediate 不由 token-root 签发）
        $token = AppleTokenFactory::makeToken(PaymentData::PAYMENT_DATA, [
            'signerCert' => $this->certPath('untrusted-leaf.crt'),
            'signerKey' => $this->certPath('untrusted-leaf.key'),
            'intermediateCert' => $this->certPath('untrusted-intermediate.crt'),
        ]);

        AppleTraitStub::verifyAppleToken($token, []);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

    it('结构非法', function () {
        $token = AppleTokenFactory::makeToken(PaymentData::PAYMENT_DATA);

        // 缺 data
        $missingData = $token;
        unset($missingData['data']);

        // transactionId 非 hex
        $badHexTransactionId = $token;
        $badHexTransactionId['header']['transactionId'] = 'zz-not-hex';

        // 非法 applicationData（非 hex）
        $badAppData = $token;
        $badAppData['header']['applicationData'] = 'not-hex!!';

        $this->expectTokenInvalidParams($missingData);
        $this->expectTokenInvalidParams($badHexTransactionId);
        $this->expectTokenInvalidParams($badAppData);
        $this->expectTokenInvalidParams('this is not json');
        $this->expectTokenInvalidParams(new Collection(['data' => 'x', 'signature' => 'x', 'version' => 'EC_v1']));
    });

    it('签名时间过期', function () {
    })->skip('signingTime 超时反路径不可测：fixture 由 openssl_pkcs7_sign 实时生成且不支持签名时间参数；'
        .'Trait 中缺失/超时均按 SIGN_ERROR 处理（真实 token 必含该属性，见 evidence）');

    it('使用默认根证书', function () {
        // 未配置 apple_root_ca：默认使用内置 AppleRootCA-G3.pem 参与链验证，
        // fixture 的 token-root 不在内置信任链内 → 验签失败（而非配置缺失异常）
        $token = AppleTokenFactory::makeToken(PaymentData::PAYMENT_DATA);

        AppleTraitStub::verifyAppleToken($token, ['_config' => 'no_root_ca']);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

    it('ASN.1 深度超限', function () {
        $token = AppleTokenFactory::makeToken(PaymentData::PAYMENT_DATA);

        // 70 层嵌套 SEQUENCE（超过 64 层上限），防恶意深嵌套 DER 栈溢出
        $der = "\x41";

        for ($i = 0; $i < 70; ++$i) {
            $len = strlen($der);
            $lenBytes = $len < 0x80 ? chr($len) : chr(0x81).chr($len);
            $der = "\x30".$lenBytes.$der;
        }

        $token['signature'] = base64_encode($der);

        AppleTraitStub::verifyAppleToken($token, []);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

    it('签名过长', function () {
        $token = AppleTokenFactory::makeToken(PaymentData::PAYMENT_DATA);

        // 超过 64KB 上限（真实 Apple token 约 3-4KB）
        $token['signature'] = base64_encode(str_repeat("\x30", 65537));

        AppleTraitStub::verifyAppleToken($token, []);
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_APPLE_TOKEN_INVALID);

    it('证书有效期校验', function () {
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
});

describe('verifyAppleJws', function () {
    it('正常', function () {
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

    it('被篡改', function () {
        $jws = AppleJwsFactory::makeJws(['notificationType' => 'SUBSCRIBED']);
        $parts = explode('.', $jws);

        $payload = (array) json_decode($this->b64UrlDecode($parts[1]), true);
        $payload['notificationType'] = 'TAMPERED';
        $tampered = $parts[0].'.'.$this->b64UrlEncode((string) json_encode($payload)).'.'.$parts[2];

        AppleTraitStub::verifyAppleJws($tampered, []);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

    it('证书链过短', function () {
        // x5c 仅 1 元素（leaf），链不完整
        $header = ['alg' => 'ES256', 'x5c' => [base64_encode($this->certDer('jws-leaf.crt'))]];
        $shortChain = $this->signWithJwsLeaf($header, ['notificationType' => 'SUBSCRIBED']);

        AppleTraitStub::verifyAppleJws($shortChain, []);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

    it('归属 bundle 不匹配', function () {
        // 归属校验：真实签名但 bundleId 与配置不匹配（防跨 App 伪造）
        $jws = AppleJwsFactory::makeJws([
            'notificationType' => 'SUBSCRIBED',
            'data' => [
                'bundleId' => 'com.evil.app',
                'environment' => 'Sandbox',
            ],
        ]);

        AppleTraitStub::verifyAppleJws($jws, []);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

    it('归属环境不匹配', function () {
        // 归属校验：environment 与配置模式不匹配（防沙盒/生产串扰）
        $jws = AppleJwsFactory::makeJws([
            'notificationType' => 'SUBSCRIBED',
            'data' => [
                'bundleId' => 'com.yansongda.pay.test',
                'environment' => 'Production',  // default 租户 mode 为 PayMode::SANDBOX
            ],
        ]);

        AppleTraitStub::verifyAppleJws($jws, []);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

    it('未配置时跳过 bundle id 校验', function () {
        // no_api_key 租户未配置 bundle_id：跳过 bundleId 校验，environment 仍须匹配
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

    it('内层缺少 x5c 被拒绝', function () {
        // 对齐官方库：二级 JWS 也须自带 x5c，无 x5c 一律拒绝（原「回退已信任 leaf 公钥」已移除）
        $inner = $this->signWithJwsLeaf(['alg' => 'ES256'], ['transactionId' => '1000001234567890']);
        $jws = AppleJwsFactory::makeJws([
            'notificationType' => 'SUBSCRIBED',
            'data' => [
                'bundleId' => 'com.yansongda.pay.test',
                'environment' => 'Sandbox',
                'signedTransactionInfo' => $inner,
            ],
        ]);

        AppleTraitStub::verifyAppleJws($jws, []);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);
});

describe('generateAppleJwt', function () {
    it('正常', function () {
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

    it('缺少 API 配置', function () {

        AppleTraitStub::generateAppleJwt(['_config' => 'no_api_key']);
    })->throws(InvalidConfigException::class, null, Exception::CONFIG_APPLE_INVALID);
});

describe('pkcs7Verify', function () {
    it('PKCS7 验签交叉校验', function () {
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
});

describe('RSA_v1', function () {
    it('PHP 8.5 以下不支持', function () {
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

        AppleTraitStub::verifyAppleToken($token, []);
    })->skip(PHP_VERSION_ID >= 80500, 'PHP 8.5 容器无法覆盖 RSA_v1 的 PHP < 8.5 版本检查分支（见 evidence）')
        ->throws(InvalidConfigException::class, null, Exception::DECRYPT_APPLE_FAILED);

    it('验证 token', function () {
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
});