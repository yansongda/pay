<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Wechat;

use Yansongda\Artful\Contract\ShortcutInterface;
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
use Yansongda\Supports\Str;

class VirtualShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $method = Str::camel($params['_action'] ?? 'default').'Plugins';

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "您所提供的 action 方法 [{$method}] 不支持，请参考文档或源码确认");
    }

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

    protected function orderQueryPlugins(): array
    {
        return $this->serverSidePlugins(QueryOrderPlugin::class);
    }

    protected function orderRefundPlugins(): array
    {
        return $this->serverSidePlugins(RefundOrderPlugin::class);
    }

    protected function orderStartDownloadPlugins(): array
    {
        return $this->serverSidePlugins(StartDownloadOrderPlugin::class);
    }

    protected function orderQueryDownloadPlugins(): array
    {
        return $this->serverSidePlugins(QueryDownloadOrderPlugin::class);
    }

    protected function orderDownloadBillPlugins(): array
    {
        return $this->serverSidePlugins(DownloadBillPlugin::class);
    }

    protected function orderNotifyProvideGoodsPlugins(): array
    {
        return $this->serverSidePlugins(NotifyProvideGoodsPlugin::class);
    }

    protected function currencyPayPlugins(): array
    {
        return $this->serverSidePlugins(CurrencyPayPlugin::class);
    }

    protected function currencyCancelPlugins(): array
    {
        return $this->serverSidePlugins(CancelCurrencyPayPlugin::class);
    }

    protected function currencyQueryBalancePlugins(): array
    {
        return $this->serverSidePlugins(QueryBalancePlugin::class);
    }

    protected function currencyPresentPlugins(): array
    {
        return $this->serverSidePlugins(PresentCurrencyPlugin::class);
    }

    protected function goodsStartUploadPlugins(): array
    {
        return $this->serverSidePlugins(StartUploadGoodsPlugin::class);
    }

    protected function goodsQueryUploadPlugins(): array
    {
        return $this->serverSidePlugins(QueryUploadGoodsPlugin::class);
    }

    protected function goodsStartPublishPlugins(): array
    {
        return $this->serverSidePlugins(StartPublishGoodsPlugin::class);
    }

    protected function goodsQueryPublishPlugins(): array
    {
        return $this->serverSidePlugins(QueryPublishGoodsPlugin::class);
    }

    protected function withdrawCreatePlugins(): array
    {
        return $this->serverSidePlugins(CreateWithdrawOrderPlugin::class);
    }

    protected function withdrawQueryPlugins(): array
    {
        return $this->serverSidePlugins(QueryWithdrawOrderPlugin::class);
    }

    protected function withdrawQueryBalancePlugins(): array
    {
        return $this->serverSidePlugins(QueryBizBalancePlugin::class);
    }

    protected function subscribeSendPrePaymentPlugins(): array
    {
        return $this->serverSidePlugins(SendSubscribePrePaymentPlugin::class);
    }

    protected function subscribeSubmitPayOrderPlugins(): array
    {
        return $this->serverSidePlugins(SubmitSubscribePayOrderPlugin::class);
    }

    protected function subscribeQueryContractPlugins(): array
    {
        return $this->serverSidePlugins(QuerySubscribeContractPlugin::class);
    }

    protected function subscribeCancelContractPlugins(): array
    {
        return $this->serverSidePlugins(CancelSubscribeContractPlugin::class);
    }

    /**
     * @param class-string $businessPlugin
     */
    protected function serverSidePlugins(string $businessPlugin): array
    {
        return [
            StartPlugin::class,
            $businessPlugin,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
