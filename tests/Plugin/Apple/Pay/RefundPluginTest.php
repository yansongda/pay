<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\RefundPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toContain('inApps/v2/refund/lookup/');
    expect($payload->get('_url'))->toContain('tx_test_456');
    expect($payload->has('transaction_id'))->toBeFalse();
});

it('URL 编码的交易号', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx id/with 特殊字符']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_url'))->toContain('/inApps/v2/refund/lookup/'.urlencode('tx id/with 特殊字符'));
});

it('过滤 payload 后保留其它字段', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', 'other' => 'keep_me']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->has('transaction_id'))->toBeFalse();
    expect($payload->get('other'))->toEqual('keep_me');
});

it('已签名交易原样透传', function () {
    $plugin = new RefundPlugin();
    // 一期设计决策：退款历史响应中的 signedTransactions（内嵌 JWS）不做自动验签，数组原样透传给调用方
    $signedTransactions = [
        'eyJhbGciOiJFUzI1NiJ9.eyJ0cmFuc2FjdGlvbklkIjoidHhfc2lnbmVkXzEifQ.sig1',
        'eyJhbGciOiJFUzI1NiJ9.eyJ0cmFuc2FjdGlvbklkIjoidHhfc2lnbmVkXzIifQ.sig2',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', 'signedTransactions' => $signedTransactions]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('signedTransactions'))->toBe($signedTransactions);
});

it('缺少交易号', function () {
    $plugin = new RefundPlugin();

    $rocket = new Rocket();
    $rocket->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('空 payload 抛出异常', function () {
    $plugin = new RefundPlugin();

    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

