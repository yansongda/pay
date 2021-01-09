<?php

declare(strict_types=1);

namespace Yansongda\Pay\Constant;

class Pay
{
    public const MODE = [
        // 正常模式
        'NORMAL' => 'normal',
        // 沙箱模式
        'SANDBOX' => 'sandbox',
        // 服务商模式
        'SERVICE' => 'service',
    ];
}
