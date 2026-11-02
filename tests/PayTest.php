<?php

declare(strict_types=1);

use Hyperf\Pimple\ContainerFactory;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Airwallex;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Jsb;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Provider\Wechat;

beforeEach(function () {
    Pay::clear();
});

afterEach(function () {
    Pay::clear();
});

it('配置', function () {
    $result = Pay::config(['name' => 'yansongda']);
    expect($result)->toBeTrue();
    expect(Pay::get(ConfigInterface::class)->get('name'))->toEqual('yansongda');
    expect(Pay::get('alipay'))->toBeInstanceOf(Alipay::class);
    expect(Pay::get(Alipay::class))->toBeInstanceOf(Alipay::class);
    expect(Pay::get('wechat'))->toBeInstanceOf(Wechat::class);
    expect(Pay::get(Wechat::class))->toBeInstanceOf(Wechat::class);
    expect(Pay::get('unipay'))->toBeInstanceOf(Unipay::class);
    expect(Pay::get(Unipay::class))->toBeInstanceOf(Unipay::class);
    expect(Pay::get('jsb'))->toBeInstanceOf(Jsb::class);
    expect(Pay::get(Jsb::class))->toBeInstanceOf(Jsb::class);

    // Task 2: without force, calling config() again returns false and does not mutate
    $result1 = Pay::config(['name' => 'yansongda1']);
    expect($result1)->toBeFalse();
    expect(Pay::get(ConfigInterface::class)->get('name'))->toEqual('yansongda');
});

it('容器已存在且未强制时返回 false', function () {
    Pay::config([
        'wechat' => [
            'default' => [
                'mch_id' => '1600314069',
                'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                'mch_secret_cert' => cert_path('wechatAppPrivateKey.pem'),
                'mch_public_cert_path' => cert_path('wechatAppPublicKey.pem'),
                'notify_url' => 'https://pay.yansongda.cn/original',
                'mp_app_id' => 'wx-original',
            ],
        ],
    ]);

    // Task 2: Without force, config() returns false and does not mutate runtime config
    $result = Pay::config([
        'wechat' => [
            'default' => [
                'mp_app_id' => 'wx-updated',
            ],
        ],
    ]);

    $wechatConfig = Pay::get(ConfigInterface::class)->get('wechat.default');

    expect($result)->toBeFalse();
    expect($wechatConfig)->toBeInstanceOf(WechatConfig::class);
    expect($wechatConfig->getMpAppId())->toBe('wx-original');
    expect($wechatConfig->getMchId())->toBe('1600314069');
    expect($wechatConfig->getNotifyUrl())->toBe('https://pay.yansongda.cn/original');
});

it('强制替换运行时配置', function () {
    Pay::config([
        'wechat' => [
            'default' => [
                'mch_id' => '1600314069',
                'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                'mch_secret_cert' => cert_path('wechatAppPrivateKey.pem'),
                'mch_public_cert_path' => cert_path('wechatAppPublicKey.pem'),
                'notify_url' => 'https://pay.yansongda.cn/original',
                'mp_app_id' => 'wx-original',
            ],
        ],
    ]);

    // Task 2: With force, config() replaces runtime config based only on new payload
    $result = Pay::config([
        '_force' => true,
        'wechat' => [
            'default' => [
                'mp_app_id' => 'wx-updated',
                'mch_id' => '1600314069',
                'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                'mch_secret_cert' => cert_path('wechatAppPrivateKey.pem'),
                'mch_public_cert_path' => cert_path('wechatAppPublicKey.pem'),
            ],
        ],
    ]);

    $wechatConfig = Pay::get(ConfigInterface::class)->get('wechat.default');

    expect($result)->toBeTrue();
    expect($wechatConfig)->toBeInstanceOf(WechatConfig::class);
    expect($wechatConfig->getMpAppId())->toBe('wx-updated');
    // notify_url was not in the new payload, so it's empty string (default) not preserved from old
    expect($wechatConfig->getNotifyUrl())->toBe('');
});

it('直接调用静态方法', function () {
    Pay::config();
    $pay = Pay::alipay();
    expect($pay)->toBeInstanceOf(Alipay::class);

    $airwallex = Pay::airwallex([['airwallex' => ['default' => ['client_id' => 'foo', 'api_key' => 'bar']]]]);
    expect($airwallex)->toBeInstanceOf(Airwallex::class);

    if (class_exists(ContainerFactory::class)) {
        Pay::clear();
        $container3 = (new ContainerFactory())();
        $pay = Pay::alipay([], $container3);

        expect(Artful::getContainer())->toBe($container3);
        expect($pay)->toBeInstanceOf(Alipay::class);
    }
});

it('设置与获取', function () {
    Pay::config(['name' => 'yansongda']);

    Pay::set('age', 28);

    expect(Pay::get('age'))->toEqual(28);
});

it('魔术调用找不到服务', function () {

    Pay::foo1([]);
})->throws(ServiceNotFoundException::class);

it('运行时配置存储类型化对象', function () {
    Pay::config([
        'wechat' => [
            'default' => [
                'mch_id' => '1600314069',
                'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                'mch_secret_cert' => cert_path('wechatAppPrivateKey.pem'),
                'mch_public_cert_path' => cert_path('wechatAppPublicKey.pem'),
                'notify_url' => 'https://pay.yansongda.cn/notify',
                'mp_app_id' => 'wx-test',
            ],
        ],
        'logger' => [
            'enable' => false,
            'file' => './logs/wechat.log',
            'level' => 'info',
        ],
        'http' => [
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
        ],
    ]);

    // Task 1 + Task 2: Provider tenant nodes are typed config objects accessible via ConfigInterface
    $wechatConfig = Pay::get(ConfigInterface::class)->get('wechat.default');
    expect($wechatConfig)->toBeInstanceOf(WechatConfig::class);
    expect($wechatConfig->getMpAppId())->toBe('wx-test');

    // Shared runtime config should remain as plain arrays
    $loggerConfig = Pay::get(ConfigInterface::class)->get('logger');
    $httpConfig = Pay::get(ConfigInterface::class)->get('http');

    expect($loggerConfig)->toBeArray();
    expect($httpConfig)->toBeArray();
    expect($loggerConfig['file'])->toBe('./logs/wechat.log');
    expect($httpConfig['timeout'])->toBe(5.0);
});

it('clear 清理证书管理器缓存', function () {
    CertManager::wechatSetCertBySerial('test', 'serial123', 'cert-content');
    expect(CertManager::wechatGetCertBySerial('test', 'serial123'))->not->toBeNull();

    Pay::clear();

    expect(CertManager::wechatGetCertBySerial('test', 'serial123'))->toBeNull();
});

