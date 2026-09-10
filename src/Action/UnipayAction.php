<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum UnipayAction: string
{
    case Default = 'default';
    case Fee = 'fee';
    case PreAuth = 'pre_auth';
    case PreOrder = 'pre_order';
    case QrCode = 'qr_code';
    case Qra = 'qra';
    case QraPos = 'qra_pos';
    case QraPosRefund = 'qra_pos_refund';
    case Web = 'web';
}
