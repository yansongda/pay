<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum UnipayCancelAction: string
{
    case Default = 'default';
    case Web = 'web';
    case QrCode = 'qr_code';
    case QraPos = 'qra_pos';
}
