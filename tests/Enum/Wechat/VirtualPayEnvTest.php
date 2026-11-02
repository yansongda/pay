<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\Wechat\VirtualPayEnv;

it('暴露环境值', function () {
    expect(VirtualPayEnv::PRODUCTION->value)->toBe(0);
    expect(VirtualPayEnv::SANDBOX->value)->toBe(1);
});

it('保持枚举声明顺序', function () {
    expect(VirtualPayEnv::cases())->toBe([
        VirtualPayEnv::PRODUCTION,
        VirtualPayEnv::SANDBOX,
    ]);
});

it('按整数值 tryFrom', function () {
    expect(VirtualPayEnv::tryFrom(0))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::tryFrom(1))->toBe(VirtualPayEnv::SANDBOX);
    expect(VirtualPayEnv::tryFrom(2))->toBeNull();
});

it('从宽松输入构造', function () {
    expect(VirtualPayEnv::fromLoose(0))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::fromLoose(1))->toBe(VirtualPayEnv::SANDBOX);
    expect(VirtualPayEnv::fromLoose('0'))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::fromLoose('1'))->toBe(VirtualPayEnv::SANDBOX);
});

it('未知宽松输入回退生产环境', function () {
    expect(VirtualPayEnv::fromLoose(2))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::fromLoose('foo'))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::fromLoose(''))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::fromLoose(null))->toBe(VirtualPayEnv::PRODUCTION);
});

it('从枚举实例构造', function () {
    expect(VirtualPayEnv::fromLoose(VirtualPayEnv::SANDBOX))->toBe(VirtualPayEnv::SANDBOX);
});

it('识别沙箱环境', function () {
    expect(VirtualPayEnv::SANDBOX->isSandbox())->toBeTrue();
    expect(VirtualPayEnv::PRODUCTION->isSandbox())->toBeFalse();
});
