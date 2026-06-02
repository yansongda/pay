<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Wechat;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
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
use Yansongda\Pay\Plugin\Wechat\Virtual\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\CreateWithdrawOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\QueryBizBalancePlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\QueryWithdrawOrderPlugin;
use Yansongda\Pay\Shortcut\Wechat\VirtualShortcut;
use Yansongda\Pay\Tests\TestCase;

class VirtualShortcutTest extends TestCase
{
    protected VirtualShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new VirtualShortcut();
    }

    public function testDefault(): void
    {
        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testOrderQuery(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'order_query']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(QueryOrderPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testOrderRefund(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'order_refund']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(RefundOrderPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testOrderStartDownload(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'order_start_download']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(StartDownloadOrderPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testOrderQueryDownload(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'order_query_download']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(QueryDownloadOrderPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testOrderDownloadBill(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'order_download_bill']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(DownloadBillPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testOrderNotifyProvideGoods(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'order_notify_provide_goods']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(NotifyProvideGoodsPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testCurrencyPay(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'currency_pay']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(CurrencyPayPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testCurrencyCancel(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'currency_cancel']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(CancelCurrencyPayPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testCurrencyQueryBalance(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'currency_query_balance']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(QueryBalancePlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testCurrencyPresent(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'currency_present']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(PresentCurrencyPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testGoodsStartUpload(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'goods_start_upload']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(StartUploadGoodsPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testGoodsQueryUpload(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'goods_query_upload']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(QueryUploadGoodsPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testGoodsStartPublish(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'goods_start_publish']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(StartPublishGoodsPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testGoodsQueryPublish(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'goods_query_publish']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(QueryPublishGoodsPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testWithdrawCreate(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'withdraw_create']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(CreateWithdrawOrderPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testWithdrawQuery(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'withdraw_query']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(QueryWithdrawOrderPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testWithdrawQueryBalance(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'withdraw_query_balance']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(QueryBizBalancePlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testSubscribeSendPrePayment(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'subscribe_send_pre_payment']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(SendSubscribePrePaymentPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testSubscribeSubmitPayOrder(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'subscribe_submit_pay_order']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(SubmitSubscribePayOrderPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testSubscribeQueryContract(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'subscribe_query_contract']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(QuerySubscribeContractPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testSubscribeCancelContract(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'subscribe_cancel_contract']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(CancelSubscribeContractPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[4]);
        self::assertSame(VerifySignaturePlugin::class, $plugins[5]);
        self::assertSame(ResponsePlugin::class, $plugins[6]);
        self::assertSame(ParserPlugin::class, $plugins[7]);
    }

    public function testInvalidAction(): void
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

        $this->plugin->getPlugins(['_action' => 'invalid_action']);
    }
}
