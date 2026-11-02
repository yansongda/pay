<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\Wechat\VirtualPayEnv;

it('exposes environment values', function () {
    expect(VirtualPayEnv::PRODUCTION->value)->toBe(0);
    expect(VirtualPayEnv::SANDBOX->value)->toBe(1);
});

it('keeps declaration order of cases', function () {
    expect(VirtualPayEnv::cases())->toBe([
        VirtualPayEnv::PRODUCTION,
        VirtualPayEnv::SANDBOX,
    ]);
});

it('tries from int value', function () {
    expect(VirtualPayEnv::tryFrom(0))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::tryFrom(1))->toBe(VirtualPayEnv::SANDBOX);
    expect(VirtualPayEnv::tryFrom(2))->toBeNull();
});

it('builds from loose input', function () {
    expect(VirtualPayEnv::fromLoose(0))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::fromLoose(1))->toBe(VirtualPayEnv::SANDBOX);
    expect(VirtualPayEnv::fromLoose('0'))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::fromLoose('1'))->toBe(VirtualPayEnv::SANDBOX);
});

it('falls back to production for unknown loose input', function () {
    expect(VirtualPayEnv::fromLoose(2))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::fromLoose('foo'))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::fromLoose(''))->toBe(VirtualPayEnv::PRODUCTION);
    expect(VirtualPayEnv::fromLoose(null))->toBe(VirtualPayEnv::PRODUCTION);
});

it('builds from loose enum instance', function () {
    expect(VirtualPayEnv::fromLoose(VirtualPayEnv::SANDBOX))->toBe(VirtualPayEnv::SANDBOX);
});

it('detects sandbox environment', function () {
    expect(VirtualPayEnv::SANDBOX->isSandbox())->toBeTrue();
    expect(VirtualPayEnv::PRODUCTION->isSandbox())->toBeFalse();
});
