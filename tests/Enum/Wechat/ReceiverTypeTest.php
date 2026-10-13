<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Enum\Wechat;

use Yansongda\Pay\Enum\Wechat\ReceiverType;
use Yansongda\Pay\Tests\TestCase;

class ReceiverTypeTest extends TestCase
{
    public function testValues(): void
    {
        self::assertSame('MERCHANT_ID', ReceiverType::MERCHANT_ID->value);
        self::assertSame('PERSONAL_OPENID', ReceiverType::PERSONAL_OPENID->value);
        self::assertSame('PERSONAL_SUB_OPENID', ReceiverType::PERSONAL_SUB_OPENID->value);
    }

    public function testCases(): void
    {
        self::assertSame(
            [
                ReceiverType::MERCHANT_ID,
                ReceiverType::PERSONAL_OPENID,
                ReceiverType::PERSONAL_SUB_OPENID,
            ],
            ReceiverType::cases()
        );
    }

    public function testTryFrom(): void
    {
        self::assertSame(ReceiverType::MERCHANT_ID, ReceiverType::tryFrom('MERCHANT_ID'));
        self::assertSame(ReceiverType::PERSONAL_OPENID, ReceiverType::tryFrom('PERSONAL_OPENID'));
        self::assertSame(ReceiverType::PERSONAL_SUB_OPENID, ReceiverType::tryFrom('PERSONAL_SUB_OPENID'));
        self::assertNull(ReceiverType::tryFrom('FOO'));
        self::assertNull(ReceiverType::tryFrom(''));
    }

    public function testFromLoose(): void
    {
        self::assertSame(ReceiverType::MERCHANT_ID, ReceiverType::fromLoose('MERCHANT_ID'));
        self::assertSame(ReceiverType::PERSONAL_OPENID, ReceiverType::fromLoose('PERSONAL_OPENID'));
    }

    public function testFromLooseReturnsNullForUnknown(): void
    {
        self::assertNull(ReceiverType::fromLoose('FOO'));
        self::assertNull(ReceiverType::fromLoose(''));
        self::assertNull(ReceiverType::fromLoose(null));
    }

    public function testFromLooseWithEnumInstance(): void
    {
        self::assertSame(ReceiverType::PERSONAL_SUB_OPENID, ReceiverType::fromLoose(ReceiverType::PERSONAL_SUB_OPENID));
    }

    public function testIsSubOpenid(): void
    {
        self::assertTrue(ReceiverType::PERSONAL_SUB_OPENID->isSubOpenid());
        self::assertFalse(ReceiverType::MERCHANT_ID->isSubOpenid());
        self::assertFalse(ReceiverType::PERSONAL_OPENID->isSubOpenid());
    }
}