<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum StripeAction: string
{
    case Default = 'default';
    case Order = 'order';
    case Refund = 'refund';
}
