<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\QueryHistoryPlugin;
use Yansongda\Supports\Collection;

it('normal', function () {
    $plugin = new QueryHistoryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toContain('/inApps/v2/history/');
    expect($payload->get('_url'))->toContain('tx_test_456');
    expect($payload->has('transaction_id'))->toBeFalse();
});

it('url encoded transaction id', function () {
    $plugin = new QueryHistoryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx id/with 特殊字符']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_url'))->toContain('/inApps/v2/history/'.urlencode('tx id/with 特殊字符'));
});

it('except payload keeps other fields', function () {
    $plugin = new QueryHistoryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', 'other' => 'keep_me']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->has('transaction_id'))->toBeFalse();
    expect($payload->get('other'))->toEqual('keep_me');
});

it('missing transaction id', function () {
    $plugin = new QueryHistoryPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: Apple 查询交易历史，缺少 transaction_id 参数');

    $rocket = new Rocket();
    $rocket->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('null payload throws exception', function () {
    $plugin = new QueryHistoryPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

