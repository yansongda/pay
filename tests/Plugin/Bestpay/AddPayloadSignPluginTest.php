<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Bestpay\V1\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin;

it('组装', function () {
    $rocket = new Rocket();
    $rocket->setParams([
        'outTradeNo' => 'ORDER001',
        'tradeAmt' => '99',
        'subject' => '测试',
    ]);

    $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);
    $rocket->mergePayload(['_url' => '/pay/tradeCreate']);

    $rocket = (new AddPayloadSignPlugin())->assembly($rocket, fn ($r) => $r);

    $payload = $rocket->getPayload();

    expect($payload->get('path'))->toEqual('/pay/tradeCreate');
    expect($payload->get('sign'))->not->toBeEmpty();
    expect($payload->get('commonParams'))->not->toBeEmpty();
    expect($payload->get('bizContent'))->not->toBeEmpty();

    $biz = json_decode((string) $payload->get('bizContent'), true);
    expect($biz['outTradeNo'])->toEqual('ORDER001');
    expect($biz['tradeAmt'])->toEqual('99');
    expect($biz['merchantNo'])->toEqual('3178033925245778');
    expect($biz)->not->toHaveKey('institutionType');
    expect($biz)->not->toHaveKey('institutionCode');
    expect($biz)->not->toHaveKey('_url');

    $common = json_decode((string) $payload->get('commonParams'), true);
    expect($common['institutionType'])->toEqual('MERCHANT');
    expect($common['institutionCode'])->toEqual('3178033925245778');
});

it('组装过滤 biz_content 中的空字段', function () {
    $rocket = new Rocket();
    $rocket->setParams([
        'outTradeNo' => 'ORDER001',
        'storeCode' => null,
        'remark' => '',
    ]);

    $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);
    $rocket->mergePayload(['_url' => '/pay/tradeCreate', 'notifyUrl' => null]);

    $rocket = (new AddPayloadSignPlugin())->assembly($rocket, fn ($r) => $r);

    $biz = json_decode((string) $rocket->getPayload()->get('bizContent'), true);

    // null 字段不进入 bizContent（对齐官方 fastjson 默认不序列化 null）
    expect($biz)->not->toHaveKey('storeCode');
    expect($biz)->not->toHaveKey('notifyUrl');
    // 空字符串保留（官方语义：签名拼为 k=，非 null）
    expect($biz['remark'])->toBe('');
});

it('组装 biz_content 不转义斜杠', function () {
    // 对齐官方 fastjson 默认序列化：URL 中的 `/` 不转义为 `\/`（JSON_UNESCAPED_SLASHES）
    $rocket = new Rocket();
    $rocket->setParams([
        'outTradeNo' => 'ORDER001',
        'notifyUrl' => 'https://pay.yansongda.cn/bestpay/notify',
        'subject' => '测试',
    ]);

    $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);
    $rocket->mergePayload(['_url' => '/pay/tradeCreate']);

    $rocket = (new AddPayloadSignPlugin())->assembly($rocket, fn ($r) => $r);

    $bizContent = (string) $rocket->getPayload()->get('bizContent');

    expect($bizContent)->toContain('"notifyUrl":"https://pay.yansongda.cn/bestpay/notify"');
    expect($bizContent)->not->toContain('\\/');
    expect($bizContent)->toContain('"subject":"测试"');
});

it('缺少路径时组装抛出异常', function () {

    $rocket = new Rocket();
    $rocket->setParams(['outTradeNo' => 'ORDER001']);

    $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);

    (new AddPayloadSignPlugin())->assembly($rocket, fn ($r) => $r);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_BESTPAY_URL_MISSING);

