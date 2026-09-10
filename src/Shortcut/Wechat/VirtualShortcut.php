<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Wechat;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Action\WechatAction;
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

class VirtualShortcut implements ShortcutInterface
{
    /**
     * @param array<string, mixed> $params
     *
     * @return array<class-string>
     *
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $action = WechatAction::tryFrom($params['_action'] ?? WechatAction::Default->value)
            ?? throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            );

        return match ($action) {
            WechatAction::Default => $this->defaultPlugins(),
            WechatAction::OrderQuery => $this->orderQueryPlugins(),
            WechatAction::OrderRefund => $this->orderRefundPlugins(),
            WechatAction::OrderStartDownload => $this->orderStartDownloadPlugins(),
            WechatAction::OrderQueryDownload => $this->orderQueryDownloadPlugins(),
            WechatAction::OrderDownloadBill => $this->orderDownloadBillPlugins(),
            WechatAction::OrderNotifyProvideGoods => $this->orderNotifyProvideGoodsPlugins(),
            WechatAction::CurrencyPay => $this->currencyPayPlugins(),
            WechatAction::CurrencyCancel => $this->currencyCancelPlugins(),
            WechatAction::CurrencyQueryBalance => $this->currencyQueryBalancePlugins(),
            WechatAction::CurrencyPresent => $this->currencyPresentPlugins(),
            WechatAction::GoodsStartUpload => $this->goodsStartUploadPlugins(),
            WechatAction::GoodsQueryUpload => $this->goodsQueryUploadPlugins(),
            WechatAction::GoodsStartPublish => $this->goodsStartPublishPlugins(),
            WechatAction::GoodsQueryPublish => $this->goodsQueryPublishPlugins(),
            WechatAction::WithdrawCreate => $this->withdrawCreatePlugins(),
            WechatAction::WithdrawQuery => $this->withdrawQueryPlugins(),
            WechatAction::WithdrawQueryBalance => $this->withdrawQueryBalancePlugins(),
            WechatAction::SubscribeSendPrePayment => $this->subscribeSendPrePaymentPlugins(),
            WechatAction::SubscribeSubmitPayOrder => $this->subscribeSubmitPayOrderPlugins(),
            WechatAction::SubscribeQueryContract => $this->subscribeQueryContractPlugins(),
            WechatAction::SubscribeCancelContract => $this->subscribeCancelContractPlugins(),
            default => throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            ),
        };
    }

    /**
     * @return array<class-string>
     */
    protected function defaultPlugins(): array
    {
        return [
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function orderQueryPlugins(): array
    {
        return $this->serverSidePlugins(QueryOrderPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function orderRefundPlugins(): array
    {
        return $this->serverSidePlugins(RefundOrderPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function orderStartDownloadPlugins(): array
    {
        return $this->serverSidePlugins(StartDownloadOrderPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function orderQueryDownloadPlugins(): array
    {
        return $this->serverSidePlugins(QueryDownloadOrderPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function orderDownloadBillPlugins(): array
    {
        return $this->serverSidePlugins(DownloadBillPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function orderNotifyProvideGoodsPlugins(): array
    {
        return $this->serverSidePlugins(NotifyProvideGoodsPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function currencyPayPlugins(): array
    {
        return $this->serverSidePlugins(CurrencyPayPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function currencyCancelPlugins(): array
    {
        return $this->serverSidePlugins(CancelCurrencyPayPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function currencyQueryBalancePlugins(): array
    {
        return $this->serverSidePlugins(QueryBalancePlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function currencyPresentPlugins(): array
    {
        return $this->serverSidePlugins(PresentCurrencyPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function goodsStartUploadPlugins(): array
    {
        return $this->serverSidePlugins(StartUploadGoodsPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function goodsQueryUploadPlugins(): array
    {
        return $this->serverSidePlugins(QueryUploadGoodsPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function goodsStartPublishPlugins(): array
    {
        return $this->serverSidePlugins(StartPublishGoodsPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function goodsQueryPublishPlugins(): array
    {
        return $this->serverSidePlugins(QueryPublishGoodsPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function withdrawCreatePlugins(): array
    {
        return $this->serverSidePlugins(CreateWithdrawOrderPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function withdrawQueryPlugins(): array
    {
        return $this->serverSidePlugins(QueryWithdrawOrderPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function withdrawQueryBalancePlugins(): array
    {
        return $this->serverSidePlugins(QueryBizBalancePlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function subscribeSendPrePaymentPlugins(): array
    {
        return $this->serverSidePlugins(SendSubscribePrePaymentPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function subscribeSubmitPayOrderPlugins(): array
    {
        return $this->serverSidePlugins(SubmitSubscribePayOrderPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function subscribeQueryContractPlugins(): array
    {
        return $this->serverSidePlugins(QuerySubscribeContractPlugin::class);
    }

    /**
     * @return array<class-string>
     */
    protected function subscribeCancelContractPlugins(): array
    {
        return $this->serverSidePlugins(CancelSubscribeContractPlugin::class);
    }

    /**
     * @param class-string $businessPlugin
     *
     * @return array<class-string>
     */
    protected function serverSidePlugins(string $businessPlugin): array
    {
        return [
            StartPlugin::class,
            $businessPlugin,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
