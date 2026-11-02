<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\Wechat\AppType;

it('exposes app type values', function () {
    expect(AppType::MP->value)->toBe('mp');
    expect(AppType::MINI->value)->toBe('mini');
    expect(AppType::APP->value)->toBe('app');
});

it('keeps declaration order of cases', function () {
    expect(AppType::cases())->toBe([
        AppType::MP,
        AppType::MINI,
        AppType::APP,
    ]);
});

it('tries from string value', function () {
    expect(AppType::tryFrom('mp'))->toBe(AppType::MP);
    expect(AppType::tryFrom('mini'))->toBe(AppType::MINI);
    expect(AppType::tryFrom('app'))->toBe(AppType::APP);
    expect(AppType::tryFrom('foo'))->toBeNull();
    expect(AppType::tryFrom(''))->toBeNull();
});

it('builds from loose input', function () {
    expect(AppType::fromLoose('mp'))->toBe(AppType::MP);
    expect(AppType::fromLoose('mini'))->toBe(AppType::MINI);
    expect(AppType::fromLoose('app'))->toBe(AppType::APP);
});

it('falls back to mp for unknown loose input', function () {
    expect(AppType::fromLoose('foo'))->toBe(AppType::MP);
    expect(AppType::fromLoose(''))->toBe(AppType::MP);
    expect(AppType::fromLoose(null))->toBe(AppType::MP);
});

it('builds from loose enum instance', function () {
    expect(AppType::fromLoose(AppType::MINI))->toBe(AppType::MINI);
    expect(AppType::fromLoose(AppType::MP))->toBe(AppType::MP);
});
