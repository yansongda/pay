<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum DouyinRefundAction: string
{
    case Default = 'default';
    case Audit = 'audit';
}
