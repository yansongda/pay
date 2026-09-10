<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum PaypalWebAction: string
{
    case Default = 'default';
    case Pay = 'pay';
    case Capture = 'capture';
}
