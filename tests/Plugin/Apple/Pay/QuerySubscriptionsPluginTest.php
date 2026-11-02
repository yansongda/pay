<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Enum\Apple\SubscriptionStatus;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\QuerySubscriptionsPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
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

it('状态覆盖', function () {
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

it('状态覆盖（标量）', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => 2]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_url'))->toContain('status=2');
    expect($payload->get('_url'))->not->toContain('%5B');
});

it('状态覆盖（枚举）', function () {
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

it('状态覆盖（标量枚举）', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'transaction_id' => 'tx_test_456',
        '_status' => SubscriptionStatus::BILLING_RETRY,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_url'))->toContain('status=3');
});

it('URL 编码的交易号', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx id/with 特殊字符']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_url'))->toContain('/inApps/v1/subscriptions/'.urlencode('tx id/with 特殊字符'));
});

it('过滤 payload 后保留其它字段', function () {
    $plugin = new QuerySubscriptionsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', 'other' => 'keep_me']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->has('transaction_id'))->toBeFalse();
    expect($payload->get('other'))->toEqual('keep_me');
});

it('非法状态抛出异常', function () {
    $plugin = new QuerySubscriptionsPlugin();

    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => [0]]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('非整数状态抛出异常', function () {
    $plugin = new QuerySubscriptionsPlugin();

    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => ['abc']]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('状态越界抛出异常', function () {
    $plugin = new QuerySubscriptionsPlugin();

    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['transaction_id' => 'tx_test_456', '_status' => [6]]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: Apple 查询订阅状态，_status 仅允许 SubscriptionStatus 枚举或 1-5 的整数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少交易号', function () {
    $plugin = new QuerySubscriptionsPlugin();

    $rocket = new Rocket();
    $rocket->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: Apple 查询订阅状态，缺少 transaction_id 参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('空 payload 抛出异常', function () {
    $plugin = new QuerySubscriptionsPlugin();

    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

