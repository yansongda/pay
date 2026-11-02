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

it('default', function () {
    $plugin = new VirtualShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        PayPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('order query', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'order_query']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(QueryOrderPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('order refund', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'order_refund']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(RefundOrderPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('order start download', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'order_start_download']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(StartDownloadOrderPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('order query download', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'order_query_download']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(QueryDownloadOrderPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('order download bill', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'order_download_bill']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(DownloadBillPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('order notify provide goods', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'order_notify_provide_goods']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(NotifyProvideGoodsPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('currency pay', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'currency_pay']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(CurrencyPayPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('currency cancel', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'currency_cancel']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(CancelCurrencyPayPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('currency query balance', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'currency_query_balance']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(QueryBalancePlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('currency present', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'currency_present']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(PresentCurrencyPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('goods start upload', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'goods_start_upload']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(StartUploadGoodsPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('goods query upload', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'goods_query_upload']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(QueryUploadGoodsPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('goods start publish', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'goods_start_publish']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(StartPublishGoodsPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('goods query publish', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'goods_query_publish']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(QueryPublishGoodsPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('withdraw create', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'withdraw_create']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(CreateWithdrawOrderPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('withdraw query', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'withdraw_query']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(QueryWithdrawOrderPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('withdraw query balance', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'withdraw_query_balance']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(QueryBizBalancePlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('subscribe send pre payment', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'subscribe_send_pre_payment']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(SendSubscribePrePaymentPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('subscribe submit pay order', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'subscribe_submit_pay_order']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(SubmitSubscribePayOrderPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('subscribe query contract', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'subscribe_query_contract']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(QuerySubscribeContractPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('subscribe cancel contract', function () {
    $plugin = new VirtualShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'subscribe_cancel_contract']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(CancelSubscribeContractPlugin::class);
    expect($plugins[4])->toBe(AddRadarPlugin::class);
    expect($plugins[5])->toBe(ResponsePlugin::class);
    expect($plugins[6])->toBe(ParserPlugin::class);
});

it('invalid action', function () {
    $plugin = new VirtualShortcut();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

    $plugin->getPlugins(['_action' => 'invalid_action']);
});

