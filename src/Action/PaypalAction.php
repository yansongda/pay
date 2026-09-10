<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum PaypalAction: string
{
    case Capture = 'capture';
    case Default = 'default';
    case Order = 'order';
    case Pay = 'pay';
    case Refund = 'refund';
}
