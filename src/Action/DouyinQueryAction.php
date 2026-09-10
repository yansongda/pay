<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum DouyinQueryAction: string
{
    case Default = 'default';
    case Order = 'order';
    case Cps = 'cps';
    case Refund = 'refund';
}
