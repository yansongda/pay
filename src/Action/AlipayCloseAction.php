<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum AlipayCloseAction: string
{
    case Default = 'default';
    case Agreement = 'agreement';
    case App = 'app';
    case Authorization = 'authorization';
    case Mini = 'mini';
    case Pos = 'pos';
    case Scan = 'scan';
    case H5 = 'h5';
    case Web = 'web';
}
