<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Enum\Wechat;

use Yansongda\Pay\Enum\Wechat\VirtualPayEnv;
use Yansongda\Pay\Tests\TestCase;

class VirtualPayEnvTest extends TestCase
{
    public function testValues(): void
    {
        self::assertSame(0, VirtualPayEnv::PRODUCTION->value);
        self::assertSame(1, VirtualPayEnv::SANDBOX->value);
    }

    public function testCases(): void
    {
        self::assertSame(
            [
                VirtualPayEnv::PRODUCTION,
                VirtualPayEnv::SANDBOX,
            ],
            VirtualPayEnv::cases()
        );
    }

    public function testTryFrom(): void
    {
        self::assertSame(VirtualPayEnv::PRODUCTION, VirtualPayEnv::tryFrom(0));
        self::assertSame(VirtualPayEnv::SANDBOX, VirtualPayEnv::tryFrom(1));
        self::assertNull(VirtualPayEnv::tryFrom(2));
    }

    public function testFromLoose(): void
    {
        self::assertSame(VirtualPayEnv::PRODUCTION, VirtualPayEnv::fromLoose(0));
        self::assertSame(VirtualPayEnv::SANDBOX, VirtualPayEnv::fromLoose(1));
        self::assertSame(VirtualPayEnv::PRODUCTION, VirtualPayEnv::fromLoose('0'));
        self::assertSame(VirtualPayEnv::SANDBOX, VirtualPayEnv::fromLoose('1'));
    }

    public function testFromLooseUnknownFallsBackToProduction(): void
    {
        self::assertSame(VirtualPayEnv::PRODUCTION, VirtualPayEnv::fromLoose(2));
        self::assertSame(VirtualPayEnv::PRODUCTION, VirtualPayEnv::fromLoose('foo'));
        self::assertSame(VirtualPayEnv::PRODUCTION, VirtualPayEnv::fromLoose(''));
        self::assertSame(VirtualPayEnv::PRODUCTION, VirtualPayEnv::fromLoose(null));
    }

    public function testFromLooseWithEnumInstance(): void
    {
        self::assertSame(VirtualPayEnv::SANDBOX, VirtualPayEnv::fromLoose(VirtualPayEnv::SANDBOX));
    }

    public function testIsSandbox(): void
    {
        self::assertTrue(VirtualPayEnv::SANDBOX->isSandbox());
        self::assertFalse(VirtualPayEnv::PRODUCTION->isSandbox());
    }
}