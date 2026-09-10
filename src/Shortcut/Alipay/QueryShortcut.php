<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Alipay;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Action\AlipayAction;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Fund\QueryPlugin as TransferQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\QueryPlugin as AgreementQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\App\QueryPlugin as AppQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\App\QueryRefundPlugin as AppQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\QueryPlugin as AuthorizationQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\QueryRefundPlugin as AuthorizationQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Face\QueryPlugin as FaceQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\QueryPlugin as H5QueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\QueryRefundPlugin as H5QueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\QueryPlugin as MiniQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\QueryRefundPlugin as MiniQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\QueryPlugin as PosQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\QueryRefundPlugin as PosQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\QueryPlugin as ScanQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\QueryRefundPlugin as ScanQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\QueryPlugin as WebQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\QueryRefundPlugin as WebQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;

class QueryShortcut implements ShortcutInterface
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
        if (isset($params['out_request_no'])) {
            return $this->refundPlugins();
        }

        $action = $params['_action'] ?? AlipayAction::QUERY_DEFAULT;

        return match ($action) {
            AlipayAction::QUERY_DEFAULT => $this->defaultPlugins(),
            AlipayAction::QUERY_AGREEMENT => $this->agreementPlugins(),
            AlipayAction::QUERY_APP => $this->appPlugins(),
            AlipayAction::QUERY_AUTHORIZATION => $this->authorizationPlugins(),
            AlipayAction::QUERY_FACE => $this->facePlugins(),
            AlipayAction::QUERY_MINI => $this->miniPlugins(),
            AlipayAction::QUERY_POS => $this->posPlugins(),
            AlipayAction::QUERY_SCAN => $this->scanPlugins(),
            AlipayAction::QUERY_H5 => $this->h5Plugins(),
            AlipayAction::QUERY_WEB => $this->webPlugins(),
            AlipayAction::QUERY_TRANSFER => $this->transferPlugins(),
            AlipayAction::QUERY_REFUND => $this->refundPlugins(),
            AlipayAction::QUERY_REFUND_APP => $this->refundAppPlugins(),
            AlipayAction::QUERY_REFUND_AUTHORIZATION => $this->refundAuthorizationPlugins(),
            AlipayAction::QUERY_REFUND_MINI => $this->refundMiniPlugins(),
            AlipayAction::QUERY_REFUND_POS => $this->refundPosPlugins(),
            AlipayAction::QUERY_REFUND_SCAN => $this->refundScanPlugins(),
            AlipayAction::QUERY_REFUND_H5 => $this->refundH5Plugins(),
            AlipayAction::QUERY_REFUND_WEB => $this->refundWebPlugins(),
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
        return $this->webPlugins();
    }

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
    protected function h5Plugins(): array
    {
        return [
            StartPlugin::class,
            H5QueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
    protected function refundPlugins(): array
    {
        return $this->refundWebPlugins();
    }

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
    protected function refundH5Plugins(): array
    {
        return [
            StartPlugin::class,
            H5QueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
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
