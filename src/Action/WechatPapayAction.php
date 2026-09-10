<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum WechatPapayAction: string
{
    case Default = 'default';
    case Order = 'order';
    case Contract = 'contract';
    case Apply = 'apply';
}
