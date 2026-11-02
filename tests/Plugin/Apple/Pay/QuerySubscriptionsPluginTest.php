<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Enum\Apple\SubscriptionStatus;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\QuerySubscriptionsPlugin;
use Yansongda\Supports\Collection;

it('normal', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();
    $url = $payload->get('_url');

    expect($payload->get('_method'))->toEqual('GET');
    expect($url)->toContain('/inApps/v1/subscriptions/');
    expect($url)->toContain('tx_test_456');
    // Apple 需要重复参数形式，禁止 http_build_query 数组生成的 %5B0%5D 形式
    expect($url)->toContain('status=1&status=4');
    expect($url)->not->toContain('%5B');
    expect($payload->has('transaction_id'))->toBeFalse();
});

it('status override', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => [3]]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();
    $url = $payload->get('_url');

    expect($url)->toContain('status=3');
    expect($url)->not->toContain('status=1');
    expect($url)->not->toContain('%5B');
});

it('status override scalar', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => 2]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_url'))->toContain('status=2');
    expect($payload->get('_url'))->not->toContain('%5B');
});

it('status override enum', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'transaction_id' => 'tx_test_456',
        '_status' => [SubscriptionStatus::EXPIRED, SubscriptionStatus::REVOKED],
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $url = $result->getPayload()->get('_url');

    expect($url)->toContain('status=2&status=5');
    expect($url)->not->toContain('status=1');
});

it('status override scalar enum', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'transaction_id' => 'tx_test_456',
        '_status' => SubscriptionStatus::BILLING_RETRY,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_url'))->toContain('status=3');
});

it('url encoded transaction id', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx id/with 特殊字符']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_url'))->toContain('/inApps/v1/subscriptions/'.urlencode('tx id/with 特殊字符'));
});

it('except payload keeps other fields', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', 'other' => 'keep_me']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->has('transaction_id'))->toBeFalse();
    expect($payload->get('other'))->toEqual('keep_me');
});

it('invalid status throws exception', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => [0]]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('non int status throws exception', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => ['abc']]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('out of range status throws exception', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: Apple 查询订阅状态，_status 仅允许 SubscriptionStatus 枚举或 1-5 的整数');

    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => [6]]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('missing transaction id', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: Apple 查询订阅状态，缺少 transaction_id 参数');

    $rocket = new Rocket();
    $rocket->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('null payload throws exception', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

