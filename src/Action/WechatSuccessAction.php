<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum WechatSuccessAction: string
{
    case Payscore = 'payscore';
    case Virtual = 'virtual';
}
