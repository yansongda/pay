<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

final class DouyinAction
{
    /* QUERY */
    public const QUERY_DEFAULT = 'default';
    public const QUERY_ORDER = 'order';
    public const QUERY_CPS = 'cps';
    public const QUERY_REFUND = 'refund';

    /* REFUND */
    public const REFUND_DEFAULT = 'default';
    public const REFUND_AUDIT = 'audit';
}
