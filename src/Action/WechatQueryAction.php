<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum WechatQueryAction: string
{
    case Default = 'default';
    case App = 'app';
    case Combine = 'combine';
    case H5 = 'h5';
    case Jsapi = 'jsapi';
    case Mini = 'mini';
    case Native = 'native';
    case Transfer = 'transfer';
    case Refund = 'refund';
    case RefundApp = 'refund_app';
    case RefundCombine = 'refund_combine';
    case RefundH5 = 'refund_h5';
    case RefundJsapi = 'refund_jsapi';
    case RefundMini = 'refund_mini';
    case RefundNative = 'refund_native';
}
