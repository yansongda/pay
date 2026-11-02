<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Douyin\V1\GetClientTokenPlugin;
use Yansongda\Supports\Collection;

it('normal', function () {
    $plugin = new GetClientTokenPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/oauth/client_token/');
    expect($payload->get('grant_type'))->toEqual('client_credential');
    expect($payload->get('client_key'))->toEqual('tt226e54d3bd581bf801');
    expect($payload->get('client_secret'))->toEqual('douyin_app_secret');
});

it('missing app id', function () {
    $plugin = new GetClientTokenPlugin();
    $config = new DouyinConfig([
        'app_id' => 'tt226e54d3bd581bf801',
        'app_secret' => 'douyin_app_secret',
    ]);
    $config->setAppId('');

    Pay::set(ConfigInterface::class, new Config([
        'douyin' => ['default' => $config],
    ]));

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_DOUYIN_INVALID);

    $plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);
});

it('missing app secret', function () {
    $plugin = new GetClientTokenPlugin();
    $config = new DouyinConfig([
        'app_id' => 'tt226e54d3bd581bf801',
        'app_secret' => 'douyin_app_secret',
    ]);
    $config->setAppSecret('');

    Pay::set(ConfigInterface::class, new Config([
        'douyin' => ['default' => $config],
    ]));

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_DOUYIN_INVALID);

    $plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);
});

it('success response', function () {
    $plugin = new GetClientTokenPlugin();
    $destination = [
        'data' => [
            'access_token' => 'clt.9f2c8a1e',
            'expires_in' => 7200,
            'error_code' => 0,
            'description' => '',
        ],
        'message' => 'success',
    ];

    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(200));
    $rocket->setDestination(new Collection($destination));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getDestination())->toBeInstanceOf(Collection::class);
    expect($result->getDestination()->all())->toEqual($destination);
});

it('business error response throws exception', function () {
    $plugin = new GetClientTokenPlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(200));
    $rocket->setDestination(new Collection([
        'data' => [
            'access_token' => '',
            'expires_in' => 0,
            'error_code' => 10013,
            'description' => 'client_key 无效',
        ],
        'message' => 'error',
    ]));

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);
    $this->expectExceptionMessage('获取抖音 client_token 失败: error_code=10013, description=client_key 无效');

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
});

it('server error throws exception', function () {
    $plugin = new GetClientTokenPlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(500));

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
});

