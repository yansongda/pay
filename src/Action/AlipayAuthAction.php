<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum AlipayAuthAction: string
{
    case TokenApp = 'token_app';
    case Query = 'query';
}
