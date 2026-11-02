<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\QueryHistoryPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
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

it('URL 编码的交易号', function () {
    $plugin = new QueryHistoryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx id/with 特殊字符']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_url'))->toContain('/inApps/v2/history/'.urlencode('tx id/with 特殊字符'));
});

it('过滤 payload 后保留其它字段', function () {
    $plugin = new QueryHistoryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', 'other' => 'keep_me']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->has('transaction_id'))->toBeFalse();
    expect($payload->get('other'))->toEqual('keep_me');
});

it('缺少交易号', function () {
    $plugin = new QueryHistoryPlugin();

    $rocket = new Rocket();
    $rocket->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: Apple 查询交易历史，缺少 transaction_id 参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('空 payload 抛出异常', function () {
    $plugin = new QueryHistoryPlugin();

    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

