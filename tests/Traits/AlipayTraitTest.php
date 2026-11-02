<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Traits\AlipayTrait;
use Yansongda\Supports\Collection;

class AlipayTraitStub
{
use AlipayTrait;
}

uses(AlipayTraitTestHelpers::class);

it('verify alipay sign success', function () {
    AlipayTraitStub::getProviderConfig('alipay');

    AlipayTraitStub::verifyAlipaySign(
        AlipayTraitStub::getProviderConfig('alipay'),
        json_encode([
            'code' => '10000',
            'msg' => 'Success',
            'order_id' => '20231220110070000002150000657610',
            'out_biz_no' => '2023122022560000',
            'pay_date' => '2023-12-20 22:56:33',
            'pay_fund_order_id' => '20231220110070001502150000660902',
            'status' => 'SUCCESS',
            'trans_amount' => '0.01',
        ], JSON_UNESCAPED_UNICODE),
        'eITxP5fZiJPB2+vZb90IRkv2iARxeNx/6Omxk7FStqflhG5lMoCvGjo2FZ6Szo1bGBMBReazZuqLaqsgomWAUO9onMVurB3enLbRvwUlpE7XEZaxk/sJYjgc2Y7pIAenvnLL9PEAiXmvUvuinUlvS9J2r1XysC0p/2wu7kEJ/GgZpFDIIYY9mdM6U1rGbi+RvirQXtQHmaEuuJWLA75NR1bvfG3L8znzW9xz1kOQqOWsQmD/bF1CDWbozNLwLCUmClRJz0Fj4mUYRF0zbW2VP8ZgHu1YvVKJ2+dWC9b+0o94URk7psIpc5NjiOM9Jsn6aoC2CfrJ/sqFMRCkYWzw6A=='
    );
    expect(true)->toBeTrue();
});

it('verify alipay sign config error', function () {
    $config1 = [
        'alipay' => [
            'default' => [
                'alipay_public_cert_path' => '',
            ],
        ],
    ];

    Pay::config(array_merge($config1, ['_force' => true]));

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);

    AlipayTraitStub::getProviderConfig('alipay');
});

it('verify alipay sign empty', function () {
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);
    AlipayTraitStub::verifyAlipaySign(AlipayTraitStub::getProviderConfig('alipay'), '', '');
});

it('get alipay url default', function () {
    expect(AlipayTraitStub::getAlipayUrl($this->getAlipayConfig(), null))->toBe(Alipay::URL[PayMode::NORMAL->value].'/gateway.do?charset=utf-8');
});

it('get alipay url sandbox', function () {
    expect(AlipayTraitStub::getAlipayUrl($this->getAlipayConfig(PayMode::SANDBOX), null))->toBe(Alipay::URL[PayMode::SANDBOX->value].'/gateway.do?charset=utf-8');
});

it('get alipay url with payload', function () {
    expect(AlipayTraitStub::getAlipayUrl($this->getAlipayConfig(), new Collection(['_url' => 'https://example.com/alipay'])))->toBe('https://example.com/alipay');
});

it('get alipay v3 url default', function () {
    expect(AlipayTraitStub::getAlipayV3Url($this->getAlipayConfig(), null))->toBe(Alipay::URL[PayMode::NORMAL->value]);
});

it('get alipay v3 url sandbox', function () {
    expect(AlipayTraitStub::getAlipayV3Url($this->getAlipayConfig(PayMode::SANDBOX), null))->toBe(Alipay::V3_SANDBOX_URL);
});

it('get alipay v3 url with payload full url', function () {
    expect(AlipayTraitStub::getAlipayV3Url($this->getAlipayConfig(), new Collection(['_url' => 'https://example.com/v3/alipay/trade/pay'])))->toBe('https://example.com/v3/alipay/trade/pay');
});

it('get alipay v3 url with payload path', function () {
    expect(AlipayTraitStub::getAlipayV3Url($this->getAlipayConfig(), new Collection(['_url' => '/v3/alipay/trade/pay'])))->toBe(Alipay::URL[PayMode::NORMAL->value].'/v3/alipay/trade/pay');
});

it('get alipay v3 authorization cert mode', function () {
    $config = AlipayTraitStub::getProviderConfig('alipay', ['_config' => 'alipay-v3']);

    $authorization = AlipayTraitStub::getAlipayV3Authorization($config, 'POST', '/v3/alipay/trade/query', '', 'test_app_auth_token');

    expect($authorization)->toContain(',app_cert_sn=');
    expect($authorization)->toContain('app_id=alipay_v3_test_app_id,');

    $authString = $this->getAuthString($authorization);
    $sign = $this->getSign($authorization);

    // 空 body 保留空行，appAuthToken 非空则作为第 5 行
    $content = $authString."\n"
        .'POST'."\n"
        .'/v3/alipay/trade/query'."\n"
        ."\n"
        .'test_app_auth_token'."\n";

    $publicKey = openssl_pkey_get_public(file_get_contents(__DIR__.'/../Cert/alipay-v3/alipay_public_cert_test.crt'));

    expect($publicKey)->not->toBeFalse();
    expect(openssl_verify($content, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256))->toBe(1);
});

it('get alipay v3 authorization missing app public cert path', function () {
    $config = new AlipayConfig([
        'app_id' => 'app_id',
        'app_secret_cert' => 'app_secret_cert',
        'alipay_public_cert_path' => 'alipay_public_cert_path',
    ], 'alipay-v3-no-app-cert');

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);
    $this->expectExceptionMessage('配置异常: 缺少支付宝配置 -- [app_public_cert_path]');

    AlipayTraitStub::getAlipayV3Authorization($config, 'POST', '/v3/alipay/trade/pay', '{}');
});

it('get alipay app public key', function () {
    $certPath = __DIR__.'/../Cert/alipayAppPublicCert.crt';
    $config = new AlipayConfig([
        'app_id' => 'app_id',
        'app_secret_cert' => 'app_secret_cert',
        'app_public_cert_path' => $certPath,
        'alipay_public_cert_path' => 'alipay_public_cert_path',
        'alipay_root_cert_path' => 'alipay_root_cert_path',
    ]);

    $appPublicKey = AlipayTraitStub::getAlipayAppPublicKey($config);

    expect($appPublicKey)->not->toContain('-----BEGIN');
    expect($appPublicKey)->not->toContain("\n");
    expect($appPublicKey)->not->toContain("\r");
    expect(base64_decode($appPublicKey, true))->not->toBeFalse();

    // 与 openssl 直接从证书提取公钥（剥离头尾后）的结果一致
    $publicKey = openssl_pkey_get_public(file_get_contents($certPath));
    expect($publicKey)->not->toBeFalse();
    $details = openssl_pkey_get_details($publicKey);
    expect($details)->not->toBeFalse();
    expect($details['key'] ?? null)->toBeString();

    expect($appPublicKey)->toBe(str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\r", "\n"], '', $details['key']));
});

it('get alipay app public key without config', function () {
    $config = new AlipayConfig([
        'app_id' => 'app_id',
        'app_secret_cert' => 'app_secret_cert',
        'app_public_cert_path' => '',
        'alipay_public_cert_path' => 'alipay_public_cert_path',
        'alipay_root_cert_path' => 'alipay_root_cert_path',
    ]);

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);
    $this->expectExceptionMessage('配置异常: 缺少支付宝配置 -- [app_public_cert_path]');

    AlipayTraitStub::getAlipayAppPublicKey($config);
});

it('verify alipay v3 sign cert mode success', function () {
    $config = AlipayTraitStub::getProviderConfig('alipay', ['_config' => 'alipay-v3']);
    $contents = "1666004496123\nyansongda-nonce\n{\"code\":\"10000\"}\n";

    openssl_sign($contents, $sign, openssl_pkey_get_private(file_get_contents(__DIR__.'/../Cert/alipay-v3/app_secret_test.pem')), OPENSSL_ALGO_SHA256);

    AlipayTraitStub::verifyAlipaySign($config, $contents, base64_encode($sign));

    expect(true)->toBeTrue();
});

it('verify alipay v3 sign error', function () {
    $config = AlipayTraitStub::getProviderConfig('alipay', ['_config' => 'alipay-v3']);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AlipayTraitStub::verifyAlipaySign($config, "1666004496123\nyansongda-nonce\n{}\n", 'invalid-sign');
});

it('verify alipay v3 sign empty', function () {
    $config = AlipayTraitStub::getProviderConfig('alipay', ['_config' => 'alipay-v3']);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);

    AlipayTraitStub::verifyAlipaySign($config, "1666004496123\nyansongda-nonce\n{}\n", '');
});

it('verify alipay sign missing alipay public cert path', function () {
    $config = new AlipayConfig([
        'app_id' => 'app_id',
        'app_secret_cert' => 'app_secret_cert',
    ], 'alipay-v3-no-public-cert');

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);
    $this->expectExceptionMessage('配置异常: 缺少支付宝配置 -- [alipay_public_cert_path]');

    AlipayTraitStub::verifyAlipaySign($config, "1666004496123\nyansongda-nonce\n{}\n", 'not-empty-sign');
});

it('decrypt alipay contents', function () {
    $plaintext = '{"code":"10000","msg":"Success","订单号":"2023122022560000","金额":"0.01"}';
    $key = base64_encode(random_bytes(16));

    $config = $this->getAlipayConfig();
    $config->setAesKey($key);

    $ciphertext = openssl_encrypt($plaintext, 'aes-128-cbc', base64_decode($key, true), OPENSSL_RAW_DATA, str_repeat("\0", 16));
    expect($ciphertext)->not->toBeFalse();

    expect(AlipayTraitStub::decryptAlipayContents(base64_encode($ciphertext), $config))->toBe($plaintext);
});

it('decrypt alipay contents without aes key', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::DECRYPT_ALIPAY_AES_KEY_INVALID);
    $this->expectExceptionMessage('加密解密异常: 未配置支付宝 AES 密钥 [aes_key]');

    AlipayTraitStub::decryptAlipayContents('not-empty-contents', $this->getAlipayConfig());
});

it('decrypt alipay contents with invalid aes key', function () {
    $config = $this->getAlipayConfig();
    $config->setAesKey(base64_encode('12345678901234567890'));

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::DECRYPT_ALIPAY_AES_KEY_INVALID);
    $this->expectExceptionMessage('加密解密异常: 支付宝 AES 密钥格式错误，须为 base64 编码的 16 字节密钥');

    AlipayTraitStub::decryptAlipayContents(base64_encode('ciphertext'), $config);
});

it('decrypt alipay contents with invalid contents', function () {
    $config = $this->getAlipayConfig();
    $config->setAesKey(base64_encode(random_bytes(16)));

    $this->expectException(DecryptException::class);
    $this->expectExceptionCode(Exception::DECRYPT_ALIPAY_ENCRYPTED_DATA_INVALID);
    $this->expectExceptionMessage('加密解密异常: 支付宝密文不是合法的 base64 字符串');

    AlipayTraitStub::decryptAlipayContents('not-base64-contents!', $config);
});

it('decrypt alipay contents with mismatched key', function () {
    $config = $this->getAlipayConfig();
    // 密钥与加密方不一致：下方固定密文由密钥 [1234567890123456] 加密生成（AES 确定性，勿改动密文与密钥的配对关系）
    $config->setAesKey(base64_encode('abcdefghijklmnop'));

    $this->expectException(DecryptException::class);
    $this->expectExceptionCode(Exception::DECRYPT_ALIPAY_ENCRYPTED_DATA_INVALID);
    $this->expectExceptionMessage('加密解密异常: 支付宝密文解密失败，请检查 AES 密钥是否与开放平台控制台一致（重新生成密钥后旧密钥立即失效）');

    AlipayTraitStub::decryptAlipayContents(
        '2U09l1AOxvuTPJp5IW4MXGCxM0a6CtXGxvrSzawMfUeU9+aFGNC9KrCMZc1LLhrjoiguutmwwqS3WWETCMC6nA==',
        $config
    );
});

trait AlipayTraitTestHelpers
{
    protected function getAlipayConfig(PayMode|int $mode = PayMode::NORMAL): AlipayConfig
    {
        return new AlipayConfig([
            'app_id' => 'app_id',
            'app_secret_cert' => 'app_secret_cert',
            'app_public_cert_path' => 'app_public_cert_path',
            'alipay_public_cert_path' => 'alipay_public_cert_path',
            'alipay_root_cert_path' => 'alipay_root_cert_path',
            'mode' => $mode,
        ], 'default');
    }
    
    private static function getAuthString(string $authorization): string
    {
        $rest = substr($authorization, strlen('ALIPAY-SHA256withRSA '));
    
        return substr($rest, 0, (int) strrpos($rest, ',sign='));
    }
    
    private static function getSign(string $authorization): string
    {
        return substr($authorization, (int) strrpos($authorization, ',sign=') + strlen(',sign='));
    }
}
