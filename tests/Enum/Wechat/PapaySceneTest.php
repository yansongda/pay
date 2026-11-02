<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\Wechat\AppType;
use Yansongda\Pay\Enum\Wechat\PapayScene;

it('exposes scene values', function () {
    expect(PapayScene::MP->value)->toBe('mp');
    expect(PapayScene::MINI->value)->toBe('mini');
    expect(PapayScene::APP->value)->toBe('app');
    expect(PapayScene::SCAN->value)->toBe('scan');
    expect(PapayScene::H5->value)->toBe('h5');
});

it('keeps declaration order of cases', function () {
    expect(PapayScene::cases())->toBe([
        PapayScene::MP,
        PapayScene::MINI,
        PapayScene::APP,
        PapayScene::SCAN,
        PapayScene::H5,
    ]);
});

it('tries from string value', function () {
    expect(PapayScene::tryFrom('mp'))->toBe(PapayScene::MP);
    expect(PapayScene::tryFrom('scan'))->toBe(PapayScene::SCAN);
    expect(PapayScene::tryFrom('h5'))->toBe(PapayScene::H5);
    expect(PapayScene::tryFrom('foo'))->toBeNull();
    expect(PapayScene::tryFrom(''))->toBeNull();
});

it('returns null for unknown loose input', function () {
    expect(PapayScene::fromLoose('foo'))->toBeNull();
    expect(PapayScene::fromLoose(''))->toBeNull();
    expect(PapayScene::fromLoose(null))->toBeNull();
});

it('builds known loose input', function () {
    expect(PapayScene::fromLoose('mini'))->toBe(PapayScene::MINI);
    expect(PapayScene::fromLoose('scan'))->toBe(PapayScene::SCAN);
});

it('builds from loose enum instance', function () {
    expect(PapayScene::fromLoose(PapayScene::H5))->toBe(PapayScene::H5);
});

it('maps scene to app type', function () {
    expect(PapayScene::MINI->appType())->toBe(AppType::MINI);
    expect(PapayScene::APP->appType())->toBe(AppType::APP);
    expect(PapayScene::MP->appType())->toBe(AppType::MP);
    expect(PapayScene::SCAN->appType())->toBe(AppType::MP);
    expect(PapayScene::H5->appType())->toBe(AppType::MP);
});

it('declares whether invocation is required', function () {
    expect(PapayScene::MP->requiresInvoke())->toBeTrue();
    expect(PapayScene::MINI->requiresInvoke())->toBeTrue();
    expect(PapayScene::APP->requiresInvoke())->toBeTrue();
    expect(PapayScene::SCAN->requiresInvoke())->toBeFalse();
    expect(PapayScene::H5->requiresInvoke())->toBeFalse();
});
