<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum WechatAction: string
{
    case App = 'app';
    case Apply = 'apply';
    case Cancel = 'cancel';
    case Combine = 'combine';
    case Complete = 'complete';
    case Contract = 'contract';
    case Create = 'create';
    case CurrencyCancel = 'currency_cancel';
    case CurrencyPay = 'currency_pay';
    case CurrencyPresent = 'currency_present';
    case CurrencyQueryBalance = 'currency_query_balance';
    case Default = 'default';
    case GoodsQueryPublish = 'goods_query_publish';
    case GoodsQueryUpload = 'goods_query_upload';
    case GoodsStartPublish = 'goods_start_publish';
    case GoodsStartUpload = 'goods_start_upload';
    case H5 = 'h5';
    case Jsapi = 'jsapi';
    case Mini = 'mini';
    case Modify = 'modify';
    case Native = 'native';
    case Order = 'order';
    case OrderDownloadBill = 'order_download_bill';
    case OrderNotifyProvideGoods = 'order_notify_provide_goods';
    case OrderQuery = 'order_query';
    case OrderQueryDownload = 'order_query_download';
    case OrderRefund = 'order_refund';
    case OrderStartDownload = 'order_start_download';
    case Pay = 'pay';
    case Payscore = 'payscore';
    case Permissions = 'permissions';
    case PermissionsQuery = 'permissions_query';
    case PermissionsTerminate = 'permissions_terminate';
    case Query = 'query';
    case Refresh = 'refresh';
    case Refund = 'refund';
    case RefundApp = 'refund_app';
    case RefundCombine = 'refund_combine';
    case RefundH5 = 'refund_h5';
    case RefundJsapi = 'refund_jsapi';
    case RefundMini = 'refund_mini';
    case RefundNative = 'refund_native';
    case Session = 'session';
    case SubscribeCancelContract = 'subscribe_cancel_contract';
    case SubscribeQueryContract = 'subscribe_query_contract';
    case SubscribeSendPrePayment = 'subscribe_send_pre_payment';
    case SubscribeSubmitPayOrder = 'subscribe_submit_pay_order';
    case Sync = 'sync';
    case Transfer = 'transfer';
    case Userinfo = 'userinfo';
    case Virtual = 'virtual';
    case WebToken = 'web_token';
    case WithdrawCreate = 'withdraw_create';
    case WithdrawQuery = 'withdraw_query';
    case WithdrawQueryBalance = 'withdraw_query_balance';
}
