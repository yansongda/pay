<?php

declare(strict_types=1);

namespace Yansongda\Pay\Enum\Alipay;

/**
 * 支付宝 `biz_content.product_code`（产品码）。
 *
 * 取值与各接口一一对应，具体含义见对应接口文档：
 * - 电脑网站支付：https://opendocs.alipay.com/open/59da99d0_alipay.trade.page.pay
 * - 手机网站支付：https://opendocs.alipay.com/open/02np8y
 * - 小程序支付：https://opendocs.alipay.com/mini/05x9kv
 */
enum ProductCode: string
{
    /** 电脑网站支付（alipay.trade.page.pay） */
    case FAST_INSTANT_TRADE_PAY = 'FAST_INSTANT_TRADE_PAY';

    /** 手机网站支付（alipay.trade.wap.pay） */
    case QUICK_WAP_WAY = 'QUICK_WAP_WAY';

    /** 小程序支付（alipay.trade.create） */
    case JSAPI_PAY = 'JSAPI_PAY';

    /** 当面付（alipay.trade.pay） */
    case FACE_TO_FACE_PAYMENT = 'FACE_TO_FACE_PAYMENT';

    /** 资金预授权/冻结（alipay.trade.pay） */
    case PREAUTH_PAY = 'PREAUTH_PAY';

    /** 周期扣款（alipay.trade.pay，协议代扣） */
    case GENERAL_WITHHOLDING = 'GENERAL_WITHHOLDING';

    /** 现金红包-APP（alipay.fund.trans.app.pay） */
    case STD_RED_PACKET = 'STD_RED_PACKET';

    /** 现金红包-网页（alipay.fund.trans.page.pay） */
    case STD_APP_TRANSFER = 'STD_APP_TRANSFER';

    /** 单笔转账到支付宝账户（alipay.fund.trans.uni.transfer） */
    case TRANS_ACCOUNT_NO_PWD = 'TRANS_ACCOUNT_NO_PWD';

    /** 会员认证初始化（alipay.user.certify.open.initialize） */
    case FACE = 'FACE';
}
