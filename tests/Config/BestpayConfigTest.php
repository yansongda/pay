<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;

it('构造合法配置', function () {
    $validConfig = [
        'merchant_no' => '1234567890',
        'institution_code' => 'INS001',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'mch_secret_cert_password' => 'secret-password',
        'bestpay_public_cert_path' => '/path/to/bestpay.pem',
    ];
    $config = new BestpayConfig($validConfig);

    expect($config->getTenant())->toBe('default');
    expect($config->getMerchantNo())->toBe('1234567890');
    expect($config->getInstitutionCode())->toBe('INS001');
    expect($config->getInstitutionType())->toBe('MERCHANT');
    expect($config->getMchSecretCertPath())->toBe('/path/to/secret.pem');
    expect($config->getMchSecretCertPassword())->toBe('secret-password');
    expect($config->getBestpayPublicCertPath())->toBe('/path/to/bestpay.pem');
    expect($config->getApiVersion())->toBe('1.0.3');
    expect($config->getNotifyUrl())->toBeNull();
    expect($config->getReturnUrl())->toBeNull();
    expect($config->getMode())->toBe(PayMode::NORMAL);
});

it('指定租户构造', function () {
    $validConfig = [
        'merchant_no' => '1234567890',
        'institution_code' => 'INS001',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'mch_secret_cert_password' => 'secret-password',
        'bestpay_public_cert_path' => '/path/to/bestpay.pem',
    ];
    $config = new BestpayConfig($validConfig, 'custom_tenant');

    expect($config->getTenant())->toBe('custom_tenant');
});

it('缺少必填项构造', function () {

    $config = new BestpayConfig([
        'merchant_no' => '1234567890',
        'institution_code' => 'INS001',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'mch_secret_cert_password' => 'secret-password',
        // missing bestpay_public_cert_path
    ]);
    $config->validate();
})->throws(InvalidConfigException::class, null, Exception::CONFIG_BESTPAY_INVALID);

it('可选 getter', function () {
    $validConfig = [
        'merchant_no' => '1234567890',
        'institution_code' => 'INS001',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'mch_secret_cert_password' => 'secret-password',
        'bestpay_public_cert_path' => '/path/to/bestpay.pem',
    ];
    $config = new BestpayConfig(array_merge($validConfig, [
        'institution_type' => 'SUB_MERCHANT',
        'api_version' => '1.0.4',
        'notify_url' => 'https://notify.com',
        'return_url' => 'https://return.com',
    ]));

    expect($config->getInstitutionType())->toBe('SUB_MERCHANT');
    expect($config->getApiVersion())->toBe('1.0.4');
    expect($config->getNotifyUrl())->toBe('https://notify.com');
    expect($config->getReturnUrl())->toBe('https://return.com');
});

it('沙箱模式', function () {
    $validConfig = [
        'merchant_no' => '1234567890',
        'institution_code' => 'INS001',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'mch_secret_cert_password' => 'secret-password',
        'bestpay_public_cert_path' => '/path/to/bestpay.pem',
    ];
    $config = new BestpayConfig(array_merge($validConfig, [
        // 兼容历史 int 写法：0/1/2
        'mode' => 1,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('使用 PayMode 枚举的模式', function () {
    $validConfig = [
        'merchant_no' => '1234567890',
        'institution_code' => 'INS001',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'mch_secret_cert_password' => 'secret-password',
        'bestpay_public_cert_path' => '/path/to/bestpay.pem',
    ];
    $config = new BestpayConfig(array_merge($validConfig, [
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('服务商模式抛出异常', function () {
    // 翼支付一期无服务商模式，mode = SERVICE 应被校验拦截
    $validConfig = [
        'merchant_no' => '1234567890',
        'institution_code' => 'INS001',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'mch_secret_cert_password' => 'secret-password',
        'bestpay_public_cert_path' => '/path/to/bestpay.pem',
    ];

    $config = new BestpayConfig(array_merge($validConfig, [
        'mode' => PayMode::SERVICE,
    ]));
    $config->validate();
})->throws(InvalidConfigException::class, '配置异常: [mode] 配置不合法', Exception::CONFIG_PROVIDER_INVALID);

