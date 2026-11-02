<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\InvokePlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new InvokePlugin();
    $fields = [
        'outOrderNo' => '20260905123456',
        'totalAmount' => 100,
        'skuList' => [
            ['skuId' => 'sku_001', 'count' => 1, 'price' => 100],
        ],
        'orderEntrySchema' => ['path' => 'pages/index/index'],
    ];

    $rocket = new Rocket();
    $rocket->setParams([]);
    $rocket->setPayload(new Collection($fields));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    // 不发送 HTTP 请求
    expect($result->getDirection())->toBe(NoHttpRequestDirection::class);

    $destination = $result->getDestination();
    expect($destination)->toBeInstanceOf(Collection::class);

    $data = $destination->get('data');
    expect($data)->toBeString();
    expect(json_encode($fields, JSON_UNESCAPED_UNICODE))->toBe($data);
    expect(json_decode($data, true))->toBe($fields);

    $byteAuthorization = $destination->get('byteAuthorization');
    expect($byteAuthorization)->toBeString();
    expect($byteAuthorization)->toStartWith('SHA256-RSA2048 ');
    expect($byteAuthorization)->toContain('appid="tt226e54d3bd581bf801"');
    expect($byteAuthorization)->toContain('key_version=1');
});

it('从输出验签', function () {
    $plugin = new InvokePlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);
    $rocket->setPayload(new Collection([
        'outOrderNo' => '20260905123456',
        'totalAmount' => 100,
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $destination = $result->getDestination();
    expect($destination)->toBeInstanceOf(Collection::class);

    $data = $destination->get('data');
    $byteAuthorization = $destination->get('byteAuthorization');
    expect($data)->toBeString();
    expect($byteAuthorization)->toBeString();

    // 从输出 byteAuthorization 反解 nonce_str/timestamp/signature（插件无注入通道）
    expect(preg_match(
        '/nonce_str="([^"]+)",timestamp="([^"]+)",key_version=1,signature="([^"]+)"/',
        $byteAuthorization,
        $matches
    ))->toBe(1);

    [, $nonce, $timestamp, $signature] = $matches;

    $contents = "POST\n/requestOrder\n".$timestamp."\n".$nonce."\n".$data."\n";
    $publicKey = openssl_pkey_get_public(file_get_contents(cert_path('douyinAppPublicKey.pem')));

    expect($publicKey)->not->toBeFalse();
    expect(openssl_verify($contents, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256))->toBe(1);
});

it('排除下划线开头的键', function () {
    $plugin = new InvokePlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);
    $rocket->setPayload(new Collection([
        '_foo' => 'bar',
        'outOrderNo' => '20260905123456',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $destination = $result->getDestination();
    expect($destination)->toBeInstanceOf(Collection::class);

    $data = $destination->get('data');
    expect($data)->toBeString();
    expect(json_decode($data, true))->toBe(['outOrderNo' => '20260905123456']);
});

it('空 payload', function () {
    $plugin = new InvokePlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('仅下划线 payload', function () {
    $plugin = new InvokePlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);
    $rocket->setPayload(new Collection(['_method' => 'POST', '_url' => '/requestOrder']));

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

