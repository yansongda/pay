<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum DouyinAction: string
{
    case Audit = 'audit';
    case Cps = 'cps';
    case Default = 'default';
    case Order = 'order';
    case Refund = 'refund';
}
