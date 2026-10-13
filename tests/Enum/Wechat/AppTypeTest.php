<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Enum\Wechat;

use Yansongda\Pay\Enum\Wechat\AppType;
use Yansongda\Pay\Tests\TestCase;

class AppTypeTest extends TestCase
{
    public function testValues(): void
    {
        self::assertSame('mp', AppType::MP->value);
        self::assertSame('mini', AppType::MINI->value);
        self::assertSame('app', AppType::APP->value);
    }

    public function testCases(): void
    {
        self::assertSame(
            [
                AppType::MP,
                AppType::MINI,
                AppType::APP,
            ],
            AppType::cases()
        );
    }

    public function testNil(): void
    {
        self::assertSame(AppType::MP, AppType::tryFrom('mp'));
        self::assertSame(AppType::MINI, AppType::tryFrom('mini'));
        self::assertSame(AppType::APP, AppType::tryFrom('app'));
        self::assertNull(AppType::tryFrom('foo'));
        self::assertNull(AppType::tryFrom(''));
    }

    public function testFromLoose(): void
    {
        self::assertSame(AppType::MP, AppType::fromLoose('mp'));
        self::assertSame(AppType::MINI, AppType::fromLoose('mini'));
        self::assertSame(AppType::APP, AppType::fromLoose('app'));
    }

    public function testFromLooseUnknownFallsBackToMp(): void
    {
        self::assertSame(AppType::MP, AppType::fromLoose('foo'));
        self::assertSame(AppType::MP, AppType::fromLoose(''));
        self::assertSame(AppType::MP, AppType::fromLoose(null));
    }

    public function testFromLooseWithEnumInstance(): void
    {
        self::assertSame(AppType::MINI, AppType::fromLoose(AppType::MINI));
        self::assertSame(AppType::MP, AppType::fromLoose(AppType::MP));
    }
}