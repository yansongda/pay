<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

final class PaypalAction
{
    /* QUERY */
    public const QUERY_DEFAULT = 'default';
    public const QUERY_ORDER = 'order';
    public const QUERY_REFUND = 'refund';

    /* WEB */
    public const WEB_DEFAULT = 'default';
    public const WEB_PAY = 'pay';
    public const WEB_CAPTURE = 'capture';
}
