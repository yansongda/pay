<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\Apple\SubscriptionStatus;

it('暴露状态值', function () {
    expect(SubscriptionStatus::ACTIVE->value)->toBe(1);
    expect(SubscriptionStatus::EXPIRED->value)->toBe(2);
    expect(SubscriptionStatus::BILLING_RETRY->value)->toBe(3);
    expect(SubscriptionStatus::BILLING_GRACE_PERIOD->value)->toBe(4);
    expect(SubscriptionStatus::REVOKED->value)->toBe(5);
});

it('保持枚举声明顺序', function () {
    expect(SubscriptionStatus::cases())->toBe([
        SubscriptionStatus::ACTIVE,
        SubscriptionStatus::EXPIRED,
        SubscriptionStatus::BILLING_RETRY,
        SubscriptionStatus::BILLING_GRACE_PERIOD,
        SubscriptionStatus::REVOKED,
    ]);
});

it('按整数值 tryFrom', function () {
    expect(SubscriptionStatus::tryFrom(1))->toBe(SubscriptionStatus::ACTIVE);
    expect(SubscriptionStatus::tryFrom(5))->toBe(SubscriptionStatus::REVOKED);
    expect(SubscriptionStatus::tryFrom(0))->toBeNull();
    expect(SubscriptionStatus::tryFrom(6))->toBeNull();
});
