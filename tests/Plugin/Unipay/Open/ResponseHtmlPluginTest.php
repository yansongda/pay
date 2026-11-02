<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Unipay\Open\ResponseHtmlPlugin;
use Yansongda\Supports\Collection;

it('HTML', function () {
    $plugin = new ResponseHtmlPlugin();
    $rocket = new Rocket();
    $rocket->setRadar(new Request('POST', 'https://yansongda.cn'))
        ->setPayload(new Collection(['name' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = (string) $result->getDestination()->getBody();

    expect($result->getDestination())->toBeInstanceOf(ResponseInterface::class);
    expect($contents)->toContain('pay_form');
    expect($contents)->toContain('yansongda');
});

it('下划线 payload', function () {
    $plugin = new ResponseHtmlPlugin();
    $rocket = new Rocket();
    $rocket->setRadar(new Request('POST', 'https://yansongda.cn'))
        ->setPayload(new Collection(['name' => 'yansongda', '_age' => 'aaaa']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = (string) $result->getDestination()->getBody();

    expect($result->getDestination())->toBeInstanceOf(ResponseInterface::class);
    expect($contents)->toContain('pay_form');
    expect($contents)->toContain('yansongda');
    expect($contents)->not->toContain('_age');
});

