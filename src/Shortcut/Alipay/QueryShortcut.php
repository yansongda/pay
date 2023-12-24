<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Alipay;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Alipay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Fund\Transfer\QueryPlugin as TransferQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Agreement\QueryPlugin as AgreementQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\App\QueryPlugin as AppQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\App\QueryRefundPlugin as AppQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Authorization\QueryPlugin as AuthorizationQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Authorization\QueryRefundPlugin as AuthorizationQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Face\QueryPlugin as FaceQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Mini\QueryPlugin as MiniQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Mini\QueryRefundPlugin as MiniQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Pos\QueryPlugin as PosQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Pos\QueryRefundPlugin as PosQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Scan\QueryPlugin as ScanQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Scan\QueryRefundPlugin as ScanQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\H5\QueryPlugin as WapQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\H5\QueryRefundPlugin as WapQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Web\QueryPlugin as WebQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Web\QueryRefundPlugin as WebQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Supports\Str;

class QueryShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $method = Str::camel($params['_action'] ?? 'default').'Plugins';

        if (isset($params['out_request_no'])) {
            return $this->refundPlugins();
        }

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "Query action [{$method}] not supported");
    }

    protected function defaultPlugins(): array
    {
        return [
            StartPlugin::class,
            WebQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function agreementPlugins(): array
    {
        return [
            StartPlugin::class,
            AgreementQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function appPlugins(): array
    {
        return [
            StartPlugin::class,
            AppQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function authorizationPlugins(): array
    {
        return [
            StartPlugin::class,
            AuthorizationQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function facePlugins(): array
    {
        return [
            StartPlugin::class,
            FaceQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function miniPlugins(): array
    {
        return [
            StartPlugin::class,
            MiniQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function posPlugins(): array
    {
        return [
            StartPlugin::class,
            PosQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function scanPlugins(): array
    {
        return [
            StartPlugin::class,
            ScanQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function wapPlugins(): array
    {
        return [
            StartPlugin::class,
            WapQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function webPlugins(): array
    {
        return [
            StartPlugin::class,
            WebQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function transferPlugins(): array
    {
        return [
            StartPlugin::class,
            TransferQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundPlugins(): array
    {
        return [
            StartPlugin::class,
            WebQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundAppPlugins(): array
    {
        return [
            StartPlugin::class,
            AppQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundAuthorizationPlugins(): array
    {
        return [
            StartPlugin::class,
            AuthorizationQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundMiniPlugins(): array
    {
        return [
            StartPlugin::class,
            MiniQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundPosPlugins(): array
    {
        return [
            StartPlugin::class,
            PosQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundScanPlugins(): array
    {
        return [
            StartPlugin::class,
            ScanQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundWapPlugins(): array
    {
        return [
            StartPlugin::class,
            WapQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundWebPlugins(): array
    {
        return [
            StartPlugin::class,
            WebQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
