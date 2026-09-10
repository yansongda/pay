<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum WechatRefundAction: string
{
    case Default = 'default';
    case App = 'app';
    case Combine = 'combine';
    case H5 = 'h5';
    case Jsapi = 'jsapi';
    case Mini = 'mini';
    case Native = 'native';
}
