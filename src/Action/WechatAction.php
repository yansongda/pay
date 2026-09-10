<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

final class WechatAction
{
    /* CALLBACK */
    public const CALLBACK_VIRTUAL = 'virtual';

    /* SUCCESS */
    public const SUCCESS_PAYSCORE = 'payscore';
    public const SUCCESS_VIRTUAL = 'virtual';

    /* PAPAY */
    public const PAPAY_DEFAULT = 'default';
    public const PAPAY_ORDER = 'order';
    public const PAPAY_CONTRACT = 'contract';
    public const PAPAY_APPLY = 'apply';

    /* PAYSCORE */
    public const PAYSCORE_DEFAULT = 'default';
    public const PAYSCORE_CREATE = 'create';
    public const PAYSCORE_QUERY = 'query';
    public const PAYSCORE_CANCEL = 'cancel';
    public const PAYSCORE_COMPLETE = 'complete';
    public const PAYSCORE_MODIFY = 'modify';
    public const PAYSCORE_SYNC = 'sync';
    public const PAYSCORE_PAY = 'pay';
    public const PAYSCORE_PERMISSIONS = 'permissions';
    public const PAYSCORE_PERMISSIONS_QUERY = 'permissions_query';
    public const PAYSCORE_PERMISSIONS_TERMINATE = 'permissions_terminate';

    /* VIRTUAL */
    public const VIRTUAL_DEFAULT = 'default';
    public const VIRTUAL_ORDER_QUERY = 'order_query';
    public const VIRTUAL_ORDER_REFUND = 'order_refund';
    public const VIRTUAL_ORDER_START_DOWNLOAD = 'order_start_download';
    public const VIRTUAL_ORDER_QUERY_DOWNLOAD = 'order_query_download';
    public const VIRTUAL_ORDER_DOWNLOAD_BILL = 'order_download_bill';
    public const VIRTUAL_ORDER_NOTIFY_PROVIDE_GOODS = 'order_notify_provide_goods';
    public const VIRTUAL_CURRENCY_PAY = 'currency_pay';
    public const VIRTUAL_CURRENCY_CANCEL = 'currency_cancel';
    public const VIRTUAL_CURRENCY_QUERY_BALANCE = 'currency_query_balance';
    public const VIRTUAL_CURRENCY_PRESENT = 'currency_present';
    public const VIRTUAL_GOODS_START_UPLOAD = 'goods_start_upload';
    public const VIRTUAL_GOODS_QUERY_UPLOAD = 'goods_query_upload';
    public const VIRTUAL_GOODS_START_PUBLISH = 'goods_start_publish';
    public const VIRTUAL_GOODS_QUERY_PUBLISH = 'goods_query_publish';
    public const VIRTUAL_WITHDRAW_CREATE = 'withdraw_create';
    public const VIRTUAL_WITHDRAW_QUERY = 'withdraw_query';
    public const VIRTUAL_WITHDRAW_QUERY_BALANCE = 'withdraw_query_balance';
    public const VIRTUAL_SUBSCRIBE_SEND_PRE_PAYMENT = 'subscribe_send_pre_payment';
    public const VIRTUAL_SUBSCRIBE_SUBMIT_PAY_ORDER = 'subscribe_submit_pay_order';
    public const VIRTUAL_SUBSCRIBE_QUERY_CONTRACT = 'subscribe_query_contract';
    public const VIRTUAL_SUBSCRIBE_CANCEL_CONTRACT = 'subscribe_cancel_contract';

    /* OAUTH */
    public const OAUTH_WEB_TOKEN = 'web_token';
    public const OAUTH_REFRESH = 'refresh';
    public const OAUTH_USERINFO = 'userinfo';
    public const OAUTH_SESSION = 'session';

    /* TRANSFER */
    public const TRANSFER_DEFAULT = 'default';
    public const TRANSFER_TRANSFER = 'transfer';

    /* CLOSE */
    public const CLOSE_DEFAULT = 'default';
    public const CLOSE_APP = 'app';
    public const CLOSE_H5 = 'h5';
    public const CLOSE_JSAPI = 'jsapi';
    public const CLOSE_MINI = 'mini';
    public const CLOSE_NATIVE = 'native';
    public const CLOSE_COMBINE = 'combine';

    /* REFUND */
    public const REFUND_DEFAULT = 'default';
    public const REFUND_APP = 'app';
    public const REFUND_COMBINE = 'combine';
    public const REFUND_H5 = 'h5';
    public const REFUND_JSAPI = 'jsapi';
    public const REFUND_MINI = 'mini';
    public const REFUND_NATIVE = 'native';

    /* CANCEL */
    public const CANCEL_DEFAULT = 'default';
    public const CANCEL_TRANSFER = 'transfer';

    /* QUERY */
    public const QUERY_DEFAULT = 'default';
    public const QUERY_APP = 'app';
    public const QUERY_COMBINE = 'combine';
    public const QUERY_H5 = 'h5';
    public const QUERY_JSAPI = 'jsapi';
    public const QUERY_MINI = 'mini';
    public const QUERY_NATIVE = 'native';
    public const QUERY_TRANSFER = 'transfer';
    public const QUERY_REFUND = 'refund';
    public const QUERY_REFUND_APP = 'refund_app';
    public const QUERY_REFUND_COMBINE = 'refund_combine';
    public const QUERY_REFUND_H5 = 'refund_h5';
    public const QUERY_REFUND_JSAPI = 'refund_jsapi';
    public const QUERY_REFUND_MINI = 'refund_mini';
    public const QUERY_REFUND_NATIVE = 'refund_native';
}
