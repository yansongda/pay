<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AppleConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;

it('构造合法配置', function () {
    $validConfig = [
        'merchant_id' => 'merchant.com.example.app',
        'payment_processing_cert' => '/path/to/payment-processing-cert.pem',
    ];
    $config = new AppleConfig(array_merge($validConfig, [
        'payment_processing_cert_passphrase' => 'passphrase',
        'apple_root_ca' => '/path/to/apple-root-ca.pem',
        'apple_intermediate_ca' => '/path/to/apple-intermediate-ca.pem',
        'issuer_id' => 'issuer-id-123',
        'bundle_id' => 'com.example.app',
        'api_key_id' => 'key-id-123',
        'api_private_key' => '/path/to/api.key',
        'notify_url' => 'https://notify.com',
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getTenant())->toBe('default');
    expect($config->getMerchantId())->toBe('merchant.com.example.app');
    expect($config->getPaymentProcessingCert())->toBe('/path/to/payment-processing-cert.pem');
    expect($config->getPaymentProcessingCertPassphrase())->toBe('passphrase');
    expect($config->getAppleRootCa())->toBe('/path/to/apple-root-ca.pem');
    expect($config->getAppleIntermediateCa())->toBe('/path/to/apple-intermediate-ca.pem');
    expect($config->getIssuerId())->toBe('issuer-id-123');
    expect($config->getBundleId())->toBe('com.example.app');
    expect($config->getApiKeyId())->toBe('key-id-123');
    expect($config->getApiPrivateKey())->toBe('/path/to/api.key');
    expect($config->getNotifyUrl())->toBe('https://notify.com');
    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('默认 Apple 根证书与中间证书', function () {
    // 未配置时回退内置证书（BUG 修复：文档宣称的「默认内置」此前为死代码）
    $validConfig = [
        'merchant_id' => 'merchant.com.example.app',
        'payment_processing_cert' => '/path/to/payment-processing-cert.pem',
    ];
    $config = new AppleConfig($validConfig);

    $rootCa = $config->getAppleRootCa();

    expect(realpath($rootCa))->toBe(realpath(dirname(__DIR__, 2).'/src/Certificate/AppleRootCA-G3.pem'));
    expect($rootCa)->toBeFile();
    expect(openssl_x509_parse((string) file_get_contents($rootCa)))->not->toBeFalse();

    $intermediateCa = $config->getAppleIntermediateCa();

    expect(realpath($intermediateCa))->toBe(realpath(dirname(__DIR__, 2).'/src/Certificate/AppleAAICAG3.pem'));
    expect($intermediateCa)->toBeFile();
    expect(openssl_x509_parse((string) file_get_contents($intermediateCa)))->not->toBeFalse();
});

it('缺少商户号构造', function () {

    $config = new AppleConfig([
        'payment_processing_cert' => '/path/to/payment-processing-cert.pem',
    ]);
    $config->validate();
})->throws(InvalidConfigException::class, '配置异常: 缺少 Apple 配置 -- [merchant_id]', Exception::CONFIG_APPLE_INVALID);

it('缺少支付处理证书构造', function () {

    $config = new AppleConfig([
        'merchant_id' => 'merchant.com.example.app',
    ]);
    $config->validate();
})->throws(InvalidConfigException::class, '配置异常: 缺少 Apple 配置 -- [payment_processing_cert]', Exception::CONFIG_APPLE_INVALID);

it('部分 API 配置构造', function () {
    $validConfig = [
        'merchant_id' => 'merchant.com.example.app',
        'payment_processing_cert' => '/path/to/payment-processing-cert.pem',
    ];

    $config = new AppleConfig(array_merge($validConfig, [
        'issuer_id' => 'issuer-id-123',
    ]));
    $config->validate();
})->throws(InvalidConfigException::class, '配置异常: Apple App Store Server API 配置不完整 -- [issuer_id]/[bundle_id]/[api_key_id]/[api_private_key] 需同时配置', Exception::CONFIG_APPLE_INVALID);

it('不传 API 配置构造', function () {
    $validConfig = [
        'merchant_id' => 'merchant.com.example.app',
        'payment_processing_cert' => '/path/to/payment-processing-cert.pem',
    ];
    $config = new AppleConfig($validConfig);

    expect($config->getIssuerId())->toBeNull();
    expect($config->getBundleId())->toBeNull();
    expect($config->getApiKeyId())->toBeNull();
    expect($config->getApiPrivateKey())->toBeNull();
    expect($config->getNotifyUrl())->toBeNull();
    // 未配置时回退内置证书（不再返回 null）
    expect($config->getAppleRootCa())->toBeFile();
    expect($config->getAppleIntermediateCa())->toBeFile();
    expect($config->getMode())->toBe(PayMode::NORMAL);

    $config->validate();
});

it('使用 PayMode 枚举的模式', function () {
    $validConfig = [
        'merchant_id' => 'merchant.com.example.app',
        'payment_processing_cert' => '/path/to/payment-processing-cert.pem',
    ];
    $config = new AppleConfig(array_merge($validConfig, [
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('非法模式抛出异常', function () {
    $validConfig = [
        'merchant_id' => 'merchant.com.example.app',
        'payment_processing_cert' => '/path/to/payment-processing-cert.pem',
    ];

    $config = new AppleConfig(array_merge($validConfig, [
        'mode' => 999999,
    ]));
    $config->validate();
})->throws(InvalidConfigException::class, null, Exception::CONFIG_PROVIDER_INVALID);

