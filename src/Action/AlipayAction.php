<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

final class AlipayAction
{
    /* CALLBACK */
    public const CALLBACK_GW = 'gw';

    /* AUTH */
    public const AUTH_TOKEN_APP = 'token_app';
    public const AUTH_QUERY = 'query';

    /* CLOSE */
    public const CLOSE_DEFAULT = 'default';
    public const CLOSE_AGREEMENT = 'agreement';
    public const CLOSE_APP = 'app';
    public const CLOSE_AUTHORIZATION = 'authorization';
    public const CLOSE_MINI = 'mini';
    public const CLOSE_POS = 'pos';
    public const CLOSE_SCAN = 'scan';
    public const CLOSE_H5 = 'h5';
    public const CLOSE_WEB = 'web';

    /* CANCEL */
    public const CANCEL_DEFAULT = 'default';
    public const CANCEL_AGREEMENT = 'agreement';
    public const CANCEL_AUTHORIZATION = 'authorization';
    public const CANCEL_MINI = 'mini';
    public const CANCEL_POS = 'pos';
    public const CANCEL_SCAN = 'scan';

    /* QUERY */
    public const QUERY_DEFAULT = 'default';
    public const QUERY_AGREEMENT = 'agreement';
    public const QUERY_APP = 'app';
    public const QUERY_AUTHORIZATION = 'authorization';
    public const QUERY_FACE = 'face';
    public const QUERY_MINI = 'mini';
    public const QUERY_POS = 'pos';
    public const QUERY_SCAN = 'scan';
    public const QUERY_H5 = 'h5';
    public const QUERY_WEB = 'web';
    public const QUERY_TRANSFER = 'transfer';
    public const QUERY_REFUND = 'refund';
    public const QUERY_REFUND_APP = 'refund_app';
    public const QUERY_REFUND_AUTHORIZATION = 'refund_authorization';
    public const QUERY_REFUND_MINI = 'refund_mini';
    public const QUERY_REFUND_POS = 'refund_pos';
    public const QUERY_REFUND_SCAN = 'refund_scan';
    public const QUERY_REFUND_H5 = 'refund_h5';
    public const QUERY_REFUND_WEB = 'refund_web';

    /* REFUND */
    public const REFUND_DEFAULT = 'default';
    public const REFUND_AGREEMENT = 'agreement';
    public const REFUND_APP = 'app';
    public const REFUND_AUTHORIZATION = 'authorization';
    public const REFUND_MINI = 'mini';
    public const REFUND_POS = 'pos';
    public const REFUND_SCAN = 'scan';
    public const REFUND_H5 = 'h5';
    public const REFUND_WEB = 'web';
    public const REFUND_TRANSFER = 'transfer';
}
