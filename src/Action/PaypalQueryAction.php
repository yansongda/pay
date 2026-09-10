<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum PaypalQueryAction: string
{
    case Default = 'default';
    case Order = 'order';
    case Refund = 'refund';
}
