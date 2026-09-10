<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum UnipayPosAction: string
{
    case Default = 'default';
    case PreAuth = 'pre_auth';
    case Qra = 'qra';
}
