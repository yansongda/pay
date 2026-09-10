<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum WechatCloseAction: string
{
    case Default = 'default';
    case App = 'app';
    case H5 = 'h5';
    case Jsapi = 'jsapi';
    case Mini = 'mini';
    case Native = 'native';
    case Combine = 'combine';
}
