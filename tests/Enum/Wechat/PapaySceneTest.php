<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\Wechat\AppType;
use Yansongda\Pay\Enum\Wechat\PapayScene;

it('暴露场景值', function () {
    expect(PapayScene::MP->value)->toBe('mp');
    expect(PapayScene::MINI->value)->toBe('mini');
    expect(PapayScene::APP->value)->toBe('app');
    expect(PapayScene::SCAN->value)->toBe('scan');
    expect(PapayScene::H5->value)->toBe('h5');
});

it('保持枚举声明顺序', function () {
    expect(PapayScene::cases())->toBe([
        PapayScene::MP,
        PapayScene::MINI,
        PapayScene::APP,
        PapayScene::SCAN,
        PapayScene::H5,
    ]);
});

it('按字符串值 tryFrom', function () {
    expect(PapayScene::tryFrom('mp'))->toBe(PapayScene::MP);
    expect(PapayScene::tryFrom('scan'))->toBe(PapayScene::SCAN);
    expect(PapayScene::tryFrom('h5'))->toBe(PapayScene::H5);
    expect(PapayScene::tryFrom('foo'))->toBeNull();
    expect(PapayScene::tryFrom(''))->toBeNull();
});

it('未知宽松输入返回 null', function () {
    expect(PapayScene::fromLoose('foo'))->toBeNull();
    expect(PapayScene::fromLoose(''))->toBeNull();
    expect(PapayScene::fromLoose(null))->toBeNull();
});

it('已知宽松输入构造', function () {
    expect(PapayScene::fromLoose('mini'))->toBe(PapayScene::MINI);
    expect(PapayScene::fromLoose('scan'))->toBe(PapayScene::SCAN);
});

it('从枚举实例构造', function () {
    expect(PapayScene::fromLoose(PapayScene::H5))->toBe(PapayScene::H5);
});

it('场景映射到 app 类型', function () {
    expect(PapayScene::MINI->appType())->toBe(AppType::MINI);
    expect(PapayScene::APP->appType())->toBe(AppType::APP);
    expect(PapayScene::MP->appType())->toBe(AppType::MP);
    expect(PapayScene::SCAN->appType())->toBe(AppType::MP);
    expect(PapayScene::H5->appType())->toBe(AppType::MP);
});

it('声明是否需要唤起', function () {
    expect(PapayScene::MP->requiresInvoke())->toBeTrue();
    expect(PapayScene::MINI->requiresInvoke())->toBeTrue();
    expect(PapayScene::APP->requiresInvoke())->toBeTrue();
    expect(PapayScene::SCAN->requiresInvoke())->toBeFalse();
    expect(PapayScene::H5->requiresInvoke())->toBeFalse();
});
