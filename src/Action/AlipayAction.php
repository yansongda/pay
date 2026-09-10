<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum AlipayAction: string
{
    case Agreement = 'agreement';
    case App = 'app';
    case Authorization = 'authorization';
    case Default = 'default';
    case Face = 'face';
    case Gw = 'gw';
    case H5 = 'h5';
    case Mini = 'mini';
    case Pos = 'pos';
    case Query = 'query';
    case Refund = 'refund';
    case RefundApp = 'refund_app';
    case RefundAuthorization = 'refund_authorization';
    case RefundH5 = 'refund_h5';
    case RefundMini = 'refund_mini';
    case RefundPos = 'refund_pos';
    case RefundScan = 'refund_scan';
    case RefundWeb = 'refund_web';
    case Scan = 'scan';
    case TokenApp = 'token_app';
    case Transfer = 'transfer';
    case Web = 'web';
}
