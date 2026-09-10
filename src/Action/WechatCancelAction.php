<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum WechatCancelAction: string
{
    case Default = 'default';
    case Transfer = 'transfer';
}
