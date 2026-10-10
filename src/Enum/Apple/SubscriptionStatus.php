<?php

declare(strict_types=1);

namespace Yansongda\Pay\Enum\Apple;

/**
 * App Store Server API 订阅状态（`status` 查询参数）。
 *
 * @see https://developer.apple.com/documentation/appstoreserverapi/status
 */
enum SubscriptionStatus: int
{
    /** 订阅处于活跃状态 */
    case ACTIVE = 1;

    /** 订阅已过期（用户可续订） */
    case EXPIRED = 2;

    /** 账单重试期（Apple 正在尝试恢复扣款） */
    case BILLING_RETRY = 3;

    /** 账单宽限期（服务仍可用） */
    case BILLING_GRACE_PERIOD = 4;

    /** 订阅已撤销（家庭共享、退款等） */
    case REVOKED = 5;
}
