<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Enum\Apple;

use Yansongda\Pay\Enum\Apple\SubscriptionStatus;
use Yansongda\Pay\Tests\TestCase;

class SubscriptionStatusTest extends TestCase
{
    public function testValues(): void
    {
        self::assertSame(1, SubscriptionStatus::ACTIVE->value);
        self::assertSame(2, SubscriptionStatus::EXPIRED->value);
        self::assertSame(3, SubscriptionStatus::BILLING_RETRY->value);
        self::assertSame(4, SubscriptionStatus::BILLING_GRACE_PERIOD->value);
        self::assertSame(5, SubscriptionStatus::REVOKED->value);
    }

    public function testCases(): void
    {
        self::assertSame(
            [
                SubscriptionStatus::ACTIVE,
                SubscriptionStatus::EXPIRED,
                SubscriptionStatus::BILLING_RETRY,
                SubscriptionStatus::BILLING_GRACE_PERIOD,
                SubscriptionStatus::REVOKED,
            ],
            SubscriptionStatus::cases()
        );
    }

    public function testTryFrom(): void
    {
        self::assertSame(SubscriptionStatus::ACTIVE, SubscriptionStatus::tryFrom(1));
        self::assertSame(SubscriptionStatus::REVOKED, SubscriptionStatus::tryFrom(5));
        self::assertNull(SubscriptionStatus::tryFrom(0));
        self::assertNull(SubscriptionStatus::tryFrom(6));
    }
}
