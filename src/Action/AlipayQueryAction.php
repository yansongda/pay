<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum AlipayQueryAction: string
{
    case Default = 'default';
    case Agreement = 'agreement';
    case App = 'app';
    case Authorization = 'authorization';
    case Face = 'face';
    case Mini = 'mini';
    case Pos = 'pos';
    case Scan = 'scan';
    case H5 = 'h5';
    case Web = 'web';
    case Transfer = 'transfer';
    case Refund = 'refund';
    case RefundApp = 'refund_app';
    case RefundAuthorization = 'refund_authorization';
    case RefundMini = 'refund_mini';
    case RefundPos = 'refund_pos';
    case RefundScan = 'refund_scan';
    case RefundH5 = 'refund_h5';
    case RefundWeb = 'refund_web';
}
