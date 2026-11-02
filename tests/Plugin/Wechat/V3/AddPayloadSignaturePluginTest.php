<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Packer\JsonPacker;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Supports\Collection;

it('获取待签名内容', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $timestamp = 1626493236;
    $random = 'QqtzdVzxavZeXag9G5mtfzbfzFMf89p6';
    $params = [
        'out_trade_no' => 1626493236,
        'description' => 'yansongda 测试 - 1626493236',
        'amount' => [
            'total' => 1,
        ],
        'scene_info' => [
            'payer_client_ip' => '127.0.0.1',
            'h5_info' => [
                'type' => 'Wap',
            ],
        ],
        'appid' => 'wx55955316af4ef13',
        'mchid' => '1600314069',
        'notify_url' => 'http://127.0.0.1:8000/wechat/notify',
    ];
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/h5',
        '_body' => (new JsonPacker())->pack($params),
    ]);
    $contents = "POST\n/v3/pay/transactions/h5\n1626493236\nQqtzdVzxavZeXag9G5mtfzbfzFMf89p6\n{\"out_trade_no\":1626493236,\"description\":\"yansongda 测试 - 1626493236\",\"amount\":{\"total\":1},\"scene_info\":{\"payer_client_ip\":\"127.0.0.1\",\"h5_info\":{\"type\":\"Wap\"}},\"appid\":\"wx55955316af4ef13\",\"mchid\":\"1600314069\",\"notify_url\":\"http:\/\/127.0.0.1:8000\/wechat\/notify\"}\n";

    $class = new ReflectionClass($plugin);
    $method = $class->getMethod('getSignatureContent');

    $result = $method->invokeArgs($plugin, [AddPayloadSignaturePlugin::getProviderConfig('wechat', $params), $payload, $timestamp, $random]);

    expect($result)->toEqual($contents);
});

it('获取签名（正常）', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $params = [
        'out_trade_no' => 1626493236,
        'description' => 'yansongda 测试 - 1626493236',
        'amount' => [
            'total' => 1,
        ],
        'scene_info' => [
            'payer_client_ip' => '127.0.0.1',
            'h5_info' => [
                'type' => 'Wap',
            ],
        ],
    ];
    $timestamp = 1626493236;
    $random = 'QqtzdVzxavZeXag9G5mtfzbfzFMf89p6';
    $contents = "POST\n/v3/pay/transactions/h5\n1626493236\nQqtzdVzxavZeXag9G5mtfzbfzFMf89p6\n{\"out_trade_no\":1626493236,\"description\":\"yansongda 测试 - 1626493236\",\"amount\":{\"total\":1},\"scene_info\":{\"payer_client_ip\":\"127.0.0.1\",\"h5_info\":{\"type\":\"Wap\"}},\"appid\":\"wx55955316af4ef13\",\"mchid\":\"1600314069\",\"notify_url\":\"http:\/\/127.0.0.1:8000\/wechat\/notify\"}\n";

    $class = new ReflectionClass($plugin);
    $method = $class->getMethod('getSignature');

    $result = $method->invokeArgs($plugin, [AddPayloadSignaturePlugin::getProviderConfig('wechat', $params), $timestamp, $random, $contents]);

    expect($result)->toEqual('WECHATPAY2-SHA256-RSA2048 mchid="1600314069",nonce_str="QqtzdVzxavZeXag9G5mtfzbfzFMf89p6",timestamp="1626493236",serial_no="25F8AA5452D55497C24BA57DC81B1E5915DC2E77",signature="KzIgMgiop3nQJNdBVR2Xah/JUwVBLDFFajyXPiSN8b8YAYEA4FuWfaCgFJ52+WFed+PhOYWx/ZPih4RaEuuSdYB8eZwYUx7RZGMQZk0bKCctAjjPuf4pJN+f/WsXKjPIy3diqF5x7gyxwSCaKWP4/KjsHNqgQpiC8q1uC5xmElzuhzSwj88LIoLtkAuSmtUVvdAt0Nz41ECHZgHWSGR32TfBo902r8afdaVKkFde8IoqcEJJcp6sMxdDO5l9R5KEWxrJ1SjsXVrb0IPH8Nj7e6hfhq7pucxojPpzsC+ZWAYvufZkAQx3kTiFmY87T+QhkP9FesOfWvkIRL4E6MP6ug=="');
});

it('获取签名缺少商户公钥证书', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $params = [
        'out_trade_no' => 1626493236,
        'description' => 'yansongda 测试 - 1626493236',
        'amount' => [
            'total' => 1,
        ],
        'scene_info' => [
            'payer_client_ip' => '127.0.0.1',
            'h5_info' => [
                'type' => 'Wap',
            ],
        ]];
    $timestamp = 1626493236;
    $random = 'QqtzdVzxavZeXag9G5mtfzbfzFMf89p6';
    $contents = "POST\n/v3/pay/transactions/h5\n1626493236\nQqtzdVzxavZeXag9G5mtfzbfzFMf89p6\n{\"out_trade_no\":1626493236,\"description\":\"yansongda 测试 - 1626493236\",\"amount\":{\"total\":1},\"scene_info\":{\"payer_client_ip\":\"127.0.0.1\",\"h5_info\":{\"type\":\"Wap\"}},\"appid\":\"wx55955316af4ef13\",\"mchid\":\"1600314069\",\"notify_url\":\"http:\/\/127.0.0.1:8000\/wechat\/notify\"}\n";

    $config = AddPayloadSignaturePlugin::getProviderConfig('wechat', $params);
    $config->setMchPublicCertPath('');

    $class = new ReflectionClass($plugin);
    $method = $class->getMethod('getSignature');
    $method->invokeArgs($plugin, [$config, $timestamp, $random, $contents]);
})->throws(InvalidConfigException::class, '配置异常: 缺少微信配置 -- [mch_public_cert_path]', Exception::CONFIG_WECHAT_INVALID);

it('使用错误商户公钥证书获取签名', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $params = [
        'out_trade_no' => 1626493236,
        'description' => 'yansongda 测试 - 1626493236',
        'amount' => [
            'total' => 1,
        ],
        'scene_info' => [
            'payer_client_ip' => '127.0.0.1',
            'h5_info' => [
                'type' => 'Wap',
            ],
        ]];
    $timestamp = 1626493236;
    $random = 'QqtzdVzxavZeXag9G5mtfzbfzFMf89p6';
    $contents = "POST\n/v3/pay/transactions/h5\n1626493236\nQqtzdVzxavZeXag9G5mtfzbfzFMf89p6\n{\"out_trade_no\":1626493236,\"description\":\"yansongda 测试 - 1626493236\",\"amount\":{\"total\":1},\"scene_info\":{\"payer_client_ip\":\"127.0.0.1\",\"h5_info\":{\"type\":\"Wap\"}},\"appid\":\"wx55955316af4ef13\",\"mchid\":\"1600314069\",\"notify_url\":\"http:\/\/127.0.0.1:8000\/wechat\/notify\"}\n";

    $config = AddPayloadSignaturePlugin::getProviderConfig('wechat', $params);
    $config->setMchPublicCertPath(cert_path('foo'));

    $class = new ReflectionClass($plugin);
    $method = $class->getMethod('getSignature');
    $method->invokeArgs($plugin, [$config, $timestamp, $random, $contents]);
})->throws(InvalidConfigException::class, '配置异常: 解析证书失败');

