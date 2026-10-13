<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Enum;

use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Tests\TestCase;

class PayModeTest extends TestCase
{
    public function testValuesAlignWithConfigStorage(): void
    {
        // 配置仍兼容 int 写法（0/1/2），枚举取值必须与其对齐（AbstractConfig 内部按 int 存储）
        self::assertSame(0, PayMode::NORMAL->value);
        self::assertSame(1, PayMode::SANDBOX->value);
        self::assertSame(2, PayMode::SERVICE->value);
    }

    public function testCases(): void
    {
        self::assertSame(
            [
                PayMode::NORMAL,
                PayMode::SANDBOX,
                PayMode::SERVICE,
            ],
            PayMode::cases()
        );
    }

    public function testTryFrom(): void
    {
        self::assertSame(PayMode::NORMAL, PayMode::tryFrom(0));
        self::assertSame(PayMode::SANDBOX, PayMode::tryFrom(1));
        self::assertSame(PayMode::SERVICE, PayMode::tryFrom(2));
        self::assertNull(PayMode::tryFrom(3));
        self::assertNull(PayMode::tryFrom(-1));
    }

    public function testFromLoose(): void
    {
        self::assertSame(PayMode::NORMAL, PayMode::fromLoose(0));
        self::assertSame(PayMode::SANDBOX, PayMode::fromLoose(1));
        self::assertSame(PayMode::SERVICE, PayMode::fromLoose('2'));
        // 未知值回落 NORMAL
        self::assertSame(PayMode::NORMAL, PayMode::fromLoose(99999));
        self::assertSame(PayMode::NORMAL, PayMode::fromLoose(null));
        self::assertSame(PayMode::SERVICE, PayMode::fromLoose(PayMode::SERVICE));
    }

    public function testIsMethods(): void
    {
        self::assertTrue(PayMode::NORMAL->isNormal());
        self::assertFalse(PayMode::NORMAL->isSandbox());
        self::assertFalse(PayMode::NORMAL->isService());

        self::assertFalse(PayMode::SANDBOX->isNormal());
        self::assertTrue(PayMode::SANDBOX->isSandbox());
        self::assertFalse(PayMode::SANDBOX->isService());

        self::assertFalse(PayMode::SERVICE->isNormal());
        self::assertFalse(PayMode::SERVICE->isSandbox());
        self::assertTrue(PayMode::SERVICE->isService());
    }
}
