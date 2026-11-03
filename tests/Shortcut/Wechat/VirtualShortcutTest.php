<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Currency\CancelCurrencyPayPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Currency\CurrencyPayPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Currency\PresentCurrencyPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Currency\QueryBalancePlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\QueryPublishGoodsPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\QueryUploadGoodsPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\StartPublishGoodsPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\StartUploadGoodsPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\DownloadBillPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\NotifyProvideGoodsPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\QueryDownloadOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\QueryOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\RefundOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\StartDownloadOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\PayPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\CancelSubscribeContractPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\QuerySubscribeContractPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\SendSubscribePrePaymentPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\SubmitSubscribePayOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\CreateWithdrawOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\QueryBizBalancePlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\QueryWithdrawOrderPlugin;
use Yansongda\Pay\Shortcut\Wechat\VirtualShortcut;

describe('order', function () {
    it('默认', function () {
        $plugin = new VirtualShortcut();
        expect($plugin->getPlugins([]))->toEqual([
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('订单查询', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'order_query']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(QueryOrderPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('订单退款', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'order_refund']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(RefundOrderPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('订单发起下载', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'order_start_download']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(StartDownloadOrderPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('订单查询下载', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'order_query_download']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(QueryDownloadOrderPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('订单下载账单', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'order_download_bill']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(DownloadBillPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('订单发货通知', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'order_notify_provide_goods']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(NotifyProvideGoodsPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });
});

describe('currency', function () {
    it('虚拟币支付', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'currency_pay']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(CurrencyPayPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('虚拟币撤销', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'currency_cancel']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(CancelCurrencyPayPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('虚拟币余额查询', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'currency_query_balance']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(QueryBalancePlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('携带虚拟币', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'currency_present']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(PresentCurrencyPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });
});

describe('goods', function () {
    it('商品发起上传', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'goods_start_upload']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(StartUploadGoodsPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('商品查询上传', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'goods_query_upload']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(QueryUploadGoodsPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('商品发起发布', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'goods_start_publish']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(StartPublishGoodsPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('商品查询发布', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'goods_query_publish']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(QueryPublishGoodsPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });
});

describe('withdraw', function () {
    it('创建提现', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'withdraw_create']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(CreateWithdrawOrderPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('提现查询', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'withdraw_query']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(QueryWithdrawOrderPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('提现余额查询', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'withdraw_query_balance']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(QueryBizBalancePlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });
});

describe('subscribe', function () {
    it('订阅发送预支付', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'subscribe_send_pre_payment']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(SendSubscribePrePaymentPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('订阅提交支付订单', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'subscribe_submit_pay_order']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(SubmitSubscribePayOrderPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('订阅查询合约', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'subscribe_query_contract']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(QuerySubscribeContractPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });

    it('订阅撤销合约', function () {
        $plugin = new VirtualShortcut();
        $plugins = $plugin->getPlugins(['_action' => 'subscribe_cancel_contract']);

        expect($plugins)->toBeArray();
        expect($plugins[0])->toBe(StartPlugin::class);
        expect($plugins[1])->toBe(CancelSubscribeContractPlugin::class);
        expect($plugins[4])->toBe(AddRadarPlugin::class);
        expect($plugins[5])->toBe(ResponsePlugin::class);
        expect($plugins[6])->toBe(ParserPlugin::class);
    });
});

describe('非法动作', function () {
    it('动作非法', function () {
        $plugin = new VirtualShortcut();

        $plugin->getPlugins(['_action' => 'invalid_action']);
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);
});