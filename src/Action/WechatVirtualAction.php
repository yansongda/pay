<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum WechatVirtualAction: string
{
    case Default = 'default';
    case OrderQuery = 'order_query';
    case OrderRefund = 'order_refund';
    case OrderStartDownload = 'order_start_download';
    case OrderQueryDownload = 'order_query_download';
    case OrderDownloadBill = 'order_download_bill';
    case OrderNotifyProvideGoods = 'order_notify_provide_goods';
    case CurrencyPay = 'currency_pay';
    case CurrencyCancel = 'currency_cancel';
    case CurrencyQueryBalance = 'currency_query_balance';
    case CurrencyPresent = 'currency_present';
    case GoodsStartUpload = 'goods_start_upload';
    case GoodsQueryUpload = 'goods_query_upload';
    case GoodsStartPublish = 'goods_start_publish';
    case GoodsQueryPublish = 'goods_query_publish';
    case WithdrawCreate = 'withdraw_create';
    case WithdrawQuery = 'withdraw_query';
    case WithdrawQueryBalance = 'withdraw_query_balance';
    case SubscribeSendPrePayment = 'subscribe_send_pre_payment';
    case SubscribeSubmitPayOrder = 'subscribe_submit_pay_order';
    case SubscribeQueryContract = 'subscribe_query_contract';
    case SubscribeCancelContract = 'subscribe_cancel_contract';
}
