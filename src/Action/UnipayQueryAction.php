<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum UnipayQueryAction: string
{
    case Default = 'default';
    case Web = 'web';
    case QrCode = 'qr_code';
    case QraPos = 'qra_pos';
    case QraPosRefund = 'qra_pos_refund';
}
