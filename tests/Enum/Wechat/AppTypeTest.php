<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\Wechat\AppType;

it('暴露应用类型值', function () {
    expect(AppType::MP->value)->toBe('mp');
    expect(AppType::MINI->value)->toBe('mini');
    expect(AppType::APP->value)->toBe('app');
});

it('保持枚举声明顺序', function () {
    expect(AppType::cases())->toBe([
        AppType::MP,
        AppType::MINI,
        AppType::APP,
    ]);
});

it('按字符串值 tryFrom', function () {
    expect(AppType::tryFrom('mp'))->toBe(AppType::MP);
    expect(AppType::tryFrom('mini'))->toBe(AppType::MINI);
    expect(AppType::tryFrom('app'))->toBe(AppType::APP);
    expect(AppType::tryFrom('foo'))->toBeNull();
    expect(AppType::tryFrom(''))->toBeNull();
});

it('从宽松输入构造', function () {
    expect(AppType::fromLoose('mp'))->toBe(AppType::MP);
    expect(AppType::fromLoose('mini'))->toBe(AppType::MINI);
    expect(AppType::fromLoose('app'))->toBe(AppType::APP);
});

it('未知宽松输入回退 mp', function () {
    expect(AppType::fromLoose('foo'))->toBe(AppType::MP);
    expect(AppType::fromLoose(''))->toBe(AppType::MP);
    expect(AppType::fromLoose(null))->toBe(AppType::MP);
});

it('从枚举实例构造', function () {
    expect(AppType::fromLoose(AppType::MINI))->toBe(AppType::MINI);
    expect(AppType::fromLoose(AppType::MP))->toBe(AppType::MP);
});
