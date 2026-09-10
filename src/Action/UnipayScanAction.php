<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum UnipayScanAction: string
{
    case Default = 'default';
    case PreAuth = 'pre_auth';
    case PreOrder = 'pre_order';
    case Fee = 'fee';
}
