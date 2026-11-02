<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\Wechat\ReceiverType;

it('暴露收款方类型值', function () {
    expect(ReceiverType::MERCHANT_ID->value)->toBe('MERCHANT_ID');
    expect(ReceiverType::PERSONAL_OPENID->value)->toBe('PERSONAL_OPENID');
    expect(ReceiverType::PERSONAL_SUB_OPENID->value)->toBe('PERSONAL_SUB_OPENID');
});

it('保持枚举声明顺序', function () {
    expect(ReceiverType::cases())->toBe([
        ReceiverType::MERCHANT_ID,
        ReceiverType::PERSONAL_OPENID,
        ReceiverType::PERSONAL_SUB_OPENID,
    ]);
});

it('按字符串值 tryFrom', function () {
    expect(ReceiverType::tryFrom('MERCHANT_ID'))->toBe(ReceiverType::MERCHANT_ID);
    expect(ReceiverType::tryFrom('PERSONAL_OPENID'))->toBe(ReceiverType::PERSONAL_OPENID);
    expect(ReceiverType::tryFrom('PERSONAL_SUB_OPENID'))->toBe(ReceiverType::PERSONAL_SUB_OPENID);
    expect(ReceiverType::tryFrom('FOO'))->toBeNull();
    expect(ReceiverType::tryFrom(''))->toBeNull();
});

it('从宽松输入构造', function () {
    expect(ReceiverType::fromLoose('MERCHANT_ID'))->toBe(ReceiverType::MERCHANT_ID);
    expect(ReceiverType::fromLoose('PERSONAL_OPENID'))->toBe(ReceiverType::PERSONAL_OPENID);
});

it('未知宽松输入返回 null', function () {
    expect(ReceiverType::fromLoose('FOO'))->toBeNull();
    expect(ReceiverType::fromLoose(''))->toBeNull();
    expect(ReceiverType::fromLoose(null))->toBeNull();
});

it('从枚举实例构造', function () {
    expect(ReceiverType::fromLoose(ReceiverType::PERSONAL_SUB_OPENID))->toBe(ReceiverType::PERSONAL_SUB_OPENID);
});

it('识别子 openid 收款方', function () {
    expect(ReceiverType::PERSONAL_SUB_OPENID->isSubOpenid())->toBeTrue();
    expect(ReceiverType::MERCHANT_ID->isSubOpenid())->toBeFalse();
    expect(ReceiverType::PERSONAL_OPENID->isSubOpenid())->toBeFalse();
});
