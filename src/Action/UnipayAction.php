<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

final class UnipayAction
{
    /* POS */
    public const POS_DEFAULT = 'default';
    public const POS_PRE_AUTH = 'pre_auth';
    public const POS_QRA = 'qra';

    /* SCAN */
    public const SCAN_DEFAULT = 'default';
    public const SCAN_PRE_AUTH = 'pre_auth';
    public const SCAN_PRE_ORDER = 'pre_order';
    public const SCAN_FEE = 'fee';

    /* QUERY */
    public const QUERY_DEFAULT = 'default';
    public const QUERY_WEB = 'web';
    public const QUERY_QR_CODE = 'qr_code';
    public const QUERY_QRA_POS = 'qra_pos';
    public const QUERY_QRA_POS_REFUND = 'qra_pos_refund';

    /* REFUND */
    public const REFUND_DEFAULT = 'default';
    public const REFUND_WEB = 'web';
    public const REFUND_QR_CODE = 'qr_code';
    public const REFUND_QRA_POS = 'qra_pos';

    /* CANCEL */
    public const CANCEL_DEFAULT = 'default';
    public const CANCEL_WEB = 'web';
    public const CANCEL_QR_CODE = 'qr_code';
    public const CANCEL_QRA_POS = 'qra_pos';
}
