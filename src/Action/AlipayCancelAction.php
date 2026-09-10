<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum AlipayCancelAction: string
{
    case Default = 'default';
    case Agreement = 'agreement';
    case Authorization = 'authorization';
    case Mini = 'mini';
    case Pos = 'pos';
    case Scan = 'scan';
}
