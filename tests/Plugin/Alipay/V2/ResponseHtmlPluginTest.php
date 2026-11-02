<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin;
use Yansongda\Supports\Collection;

it('重定向', function () {
    $plugin = new ResponseHtmlPlugin();
    $rocket = new Rocket();
    $rocket->setRadar(new Request('GET', 'https://yansongda.cn'))
        ->setPayload(new Collection(['name' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination())->toBeInstanceOf(ResponseInterface::class);
    expect($result->getDestination()->getHeaders())->toHaveKey('Location');
    expect($result->getDestination()->getHeaderLine('Location'))->toEqual('https://yansongda.cn?name=yansongda');
});

it('重定向包含标记', function () {
    $plugin = new ResponseHtmlPlugin();
    $rocket = new Rocket();
    $rocket->setRadar(new Request('GET', 'https://yansongda.cn?charset=utf8'))
        ->setPayload(new Collection(['name' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination())->toBeInstanceOf(ResponseInterface::class);
    expect($result->getDestination()->getHeaders())->toHaveKey('Location');
    expect($result->getDestination()->getHeaderLine('Location'))->toEqual('https://yansongda.cn?charset=utf8&name=yansongda');
});

it('HTML', function () {
    $plugin = new ResponseHtmlPlugin();
    $rocket = new Rocket();
    $rocket->setRadar(new Request('POST', 'https://yansongda.cn'))
        ->setPayload(new Collection(['name' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = (string) $result->getDestination()->getBody();

    expect($result->getDestination())->toBeInstanceOf(ResponseInterface::class);
    expect($contents)->toContain('alipay_submit');
    expect($contents)->toContain('yansongda');
});

