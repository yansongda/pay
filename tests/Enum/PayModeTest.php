<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\PayMode;

it('枚举值与配置存储对齐', function () {
    // 配置仍兼容 int 写法（0/1/2），枚举取值必须与其对齐（AbstractConfig 内部按 int 存储）
    expect(PayMode::NORMAL->value)->toBe(0);
    expect(PayMode::SANDBOX->value)->toBe(1);
    expect(PayMode::SERVICE->value)->toBe(2);
});

it('保持枚举声明顺序', function () {
    expect(PayMode::cases())->toBe([
        PayMode::NORMAL,
        PayMode::SANDBOX,
        PayMode::SERVICE,
    ]);
});

it('按整数值 tryFrom', function () {
    expect(PayMode::tryFrom(0))->toBe(PayMode::NORMAL);
    expect(PayMode::tryFrom(1))->toBe(PayMode::SANDBOX);
    expect(PayMode::tryFrom(2))->toBe(PayMode::SERVICE);
    expect(PayMode::tryFrom(3))->toBeNull();
    expect(PayMode::tryFrom(-1))->toBeNull();
});

it('从宽松输入构造', function () {
    expect(PayMode::fromLoose(0))->toBe(PayMode::NORMAL);
    expect(PayMode::fromLoose(1))->toBe(PayMode::SANDBOX);
    expect(PayMode::fromLoose('2'))->toBe(PayMode::SERVICE);
    // 未知值回落 NORMAL
    expect(PayMode::fromLoose(99999))->toBe(PayMode::NORMAL);
    expect(PayMode::fromLoose(null))->toBe(PayMode::NORMAL);
    expect(PayMode::fromLoose(PayMode::SERVICE))->toBe(PayMode::SERVICE);
});

it('判断模式类型', function () {
    expect(PayMode::NORMAL->isNormal())->toBeTrue();
    expect(PayMode::NORMAL->isSandbox())->toBeFalse();
    expect(PayMode::NORMAL->isService())->toBeFalse();

    expect(PayMode::SANDBOX->isNormal())->toBeFalse();
    expect(PayMode::SANDBOX->isSandbox())->toBeTrue();
    expect(PayMode::SANDBOX->isService())->toBeFalse();

    expect(PayMode::SERVICE->isNormal())->toBeFalse();
    expect(PayMode::SERVICE->isSandbox())->toBeFalse();
    expect(PayMode::SERVICE->isService())->toBeTrue();
});
