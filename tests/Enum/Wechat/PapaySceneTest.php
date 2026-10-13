<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Enum\Wechat;

use Yansongda\Pay\Enum\Wechat\AppType;
use Yansongda\Pay\Enum\Wechat\PapayScene;
use Yansongda\Pay\Tests\TestCase;

class PapaySceneTest extends TestCase
{
    public function testValues(): void
    {
        self::assertSame('mp', PapayScene::MP->value);
        self::assertSame('mini', PapayScene::MINI->value);
        self::assertSame('app', PapayScene::APP->value);
        self::assertSame('scan', PapayScene::SCAN->value);
        self::assertSame('h5', PapayScene::H5->value);
    }

    public function testCases(): void
    {
        self::assertSame(
            [
                PapayScene::MP,
                PapayScene::MINI,
                PapayScene::APP,
                PapayScene::SCAN,
                PapayScene::H5,
            ],
            PapayScene::cases()
        );
    }

    public function testTryFrom(): void
    {
        self::assertSame(PapayScene::MP, PapayScene::tryFrom('mp'));
        self::assertSame(PapayScene::SCAN, PapayScene::tryFrom('scan'));
        self::assertSame(PapayScene::H5, PapayScene::tryFrom('h5'));
        self::assertNull(PapayScene::tryFrom('foo'));
        self::assertNull(PapayScene::tryFrom(''));
    }

    public function testFromLooseReturnsNullForUnknown(): void
    {
        self::assertNull(PapayScene::fromLoose('foo'));
        self::assertNull(PapayScene::fromLoose(''));
        self::assertNull(PapayScene::fromLoose(null));
    }

    public function testFromLooseKnown(): void
    {
        self::assertSame(PapayScene::MINI, PapayScene::fromLoose('mini'));
        self::assertSame(PapayScene::SCAN, PapayScene::fromLoose('scan'));
    }

    public function testFromLooseWithEnumInstance(): void
    {
        self::assertSame(PapayScene::H5, PapayScene::fromLoose(PapayScene::H5));
    }

    public function testAppType(): void
    {
        self::assertSame(AppType::MINI, PapayScene::MINI->appType());
        self::assertSame(AppType::APP, PapayScene::APP->appType());
        self::assertSame(AppType::MP, PapayScene::MP->appType());
        self::assertSame(AppType::MP, PapayScene::SCAN->appType());
        self::assertSame(AppType::MP, PapayScene::H5->appType());
    }

    public function testRequiresInvoke(): void
    {
        self::assertTrue(PapayScene::MP->requiresInvoke());
        self::assertTrue(PapayScene::MINI->requiresInvoke());
        self::assertTrue(PapayScene::APP->requiresInvoke());
        self::assertFalse(PapayScene::SCAN->requiresInvoke());
        self::assertFalse(PapayScene::H5->requiresInvoke());
    }
}