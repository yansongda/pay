<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\Wechat\ReceiverType;

it('exposes receiver type values', function () {
    expect(ReceiverType::MERCHANT_ID->value)->toBe('MERCHANT_ID');
    expect(ReceiverType::PERSONAL_OPENID->value)->toBe('PERSONAL_OPENID');
    expect(ReceiverType::PERSONAL_SUB_OPENID->value)->toBe('PERSONAL_SUB_OPENID');
});

it('keeps declaration order of cases', function () {
    expect(ReceiverType::cases())->toBe([
        ReceiverType::MERCHANT_ID,
        ReceiverType::PERSONAL_OPENID,
        ReceiverType::PERSONAL_SUB_OPENID,
    ]);
});

it('tries from string value', function () {
    expect(ReceiverType::tryFrom('MERCHANT_ID'))->toBe(ReceiverType::MERCHANT_ID);
    expect(ReceiverType::tryFrom('PERSONAL_OPENID'))->toBe(ReceiverType::PERSONAL_OPENID);
    expect(ReceiverType::tryFrom('PERSONAL_SUB_OPENID'))->toBe(ReceiverType::PERSONAL_SUB_OPENID);
    expect(ReceiverType::tryFrom('FOO'))->toBeNull();
    expect(ReceiverType::tryFrom(''))->toBeNull();
});

it('builds from loose input', function () {
    expect(ReceiverType::fromLoose('MERCHANT_ID'))->toBe(ReceiverType::MERCHANT_ID);
    expect(ReceiverType::fromLoose('PERSONAL_OPENID'))->toBe(ReceiverType::PERSONAL_OPENID);
});

it('returns null for unknown loose input', function () {
    expect(ReceiverType::fromLoose('FOO'))->toBeNull();
    expect(ReceiverType::fromLoose(''))->toBeNull();
    expect(ReceiverType::fromLoose(null))->toBeNull();
});

it('builds from loose enum instance', function () {
    expect(ReceiverType::fromLoose(ReceiverType::PERSONAL_SUB_OPENID))->toBe(ReceiverType::PERSONAL_SUB_OPENID);
});

it('detects sub openid receiver', function () {
    expect(ReceiverType::PERSONAL_SUB_OPENID->isSubOpenid())->toBeTrue();
    expect(ReceiverType::MERCHANT_ID->isSubOpenid())->toBeFalse();
    expect(ReceiverType::PERSONAL_OPENID->isSubOpenid())->toBeFalse();
});
