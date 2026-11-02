<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Virtual\PayPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'buyQuantity' => 1,
        'productId' => 'test_product',
        'goodsPrice' => 10,
        'outTradeNo' => '20240101000000',
        'attach' => 'custom_attach',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('requestVirtualPayment');
    expect($payload->get('offerId'))->toEqual('1234567890');
    expect($payload->get('buyQuantity'))->toEqual(1);
    expect($payload->get('env'))->toBeNull();
    expect($payload->get('currencyType'))->toEqual('CNY');
    expect($payload->get('productId'))->toEqual('test_product');
    expect($payload->get('goodsPrice'))->toEqual(10);
    expect($payload->get('outTradeNo'))->toEqual('20240101000000');
    expect($payload->get('attach'))->toEqual('custom_attach');

    // destination 应为 payload（NoHttpRequestDirection 场景）
    expect($result->getDestination())->toBe($payload);
});

it('沙箱环境', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'buyQuantity' => 1,
        'env' => 1,
        'productId' => 'test_product',
        'goodsPrice' => 10,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
    expect($payload->get('_url'))->toEqual('requestVirtualPayment');
});

it('自定义 URL 覆盖默认值', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        '_url' => '/xpay/pay_deposit',
        'buyQuantity' => 1,
        'productId' => 'test_product',
        'goodsPrice' => 10,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_url'))->toEqual('/xpay/pay_deposit');
});

it('默认值', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'buyQuantity' => 1,
        'productId' => 'test_product',
        'goodsPrice' => 10,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toBeNull();
    expect($payload->get('currencyType'))->toEqual('CNY');
    expect($payload->get('outTradeNo'))->toBeEmpty();
    expect($payload->get('attach'))->toBeEmpty();
});

it('缺少 offer_id 抛出异常', function () {
    Pay::clear();
    Pay::config([
        'wechat' => [
            'default' => [
                'app_id' => 'yansongda',
                'mp_app_id' => 'wx55955316af4ef13',
                'mch_id' => '1600314069',
                'mini_app_id' => 'wx55955316af4ef14',
                'mch_secret_key_v2' => 'yansongda',
                'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                'mch_secret_cert' => cert_path('wechatAppPrivateKey.pem'),
                'mch_public_cert_path' => cert_path('wechatAppPublicKey.pem'),
                'notify_url' => 'https://pay.yansongda.cn',
                'wechat_public_cert_path' => [
                    '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => cert_path('wechatAppPublicKey.pem'),
                ],
                'mode' => PayMode::NORMAL,
                'virtual_pay' => [
                    'app_key' => 'yansongda_virtual_pay',
                    'sandbox_app_key' => 'yansongda_virtual_pay_sandbox',
                    'encoding_aes_key' => 'MDEyMzQ1Njc4OWFiY2RlZjAxMjM0NTY3ODlhYmNkZWY',
                    'callback_token' => 'test_callback_token',
                ],
            ],
        ],
    ]);

    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'buyQuantity' => 1,
        'productId' => 'test_product',
        'goodsPrice' => 10,
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidConfigException::class, null, Exception::CONFIG_WECHAT_INVALID);

it('客户端签名 payload 按 key 排序', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'outTradeNo' => '20240101000000',
        'buyQuantity' => 1,
        'goodsPrice' => 10,
        'productId' => 'test_product',
        'attach' => 'custom_attach',
        'env' => 0,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();
    $items = $payload->all();

    // 过滤 _ 前缀的 meta 键，只取业务键
    $businessKeys = [];
    foreach ($items as $key => $value) {
        if (!str_starts_with((string) $key, '_')) {
            $businessKeys[] = $key;
        }
    }

    // 断言业务键为字典序排列
    $expected = ['attach', 'buyQuantity', 'currencyType', 'env', 'goodsPrice', 'offerId', 'outTradeNo', 'productId'];
    expect($businessKeys)->toBe($expected);
});

it('嵌套数组递归排序', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'buyQuantity' => 1,
        'productId' => 'test_product',
        'goodsPrice' => 10,
        'ext' => [
            'z_key' => 'z_value',
            'a_key' => 'a_value',
            'm_key' => [
                'b_sub' => 'b',
                'a_sub' => 'a',
            ],
        ],
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();
    $ext = $payload->get('ext');

    expect($ext)->toBeArray();

    // 外层键排序后应为 a_key, m_key, z_key
    expect(array_keys($ext))->toBe(['a_key', 'm_key', 'z_key']);

    // 嵌套数组键也应排序
    expect(array_keys($ext['m_key']))->toBe(['a_sub', 'b_sub']);
});

