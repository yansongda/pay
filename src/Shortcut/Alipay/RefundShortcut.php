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
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\RefundPlugin as FundTransferRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\RefundPlugin as AgreementRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\App\RefundPlugin as AppRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\RefundPlugin as AuthorizationRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\RefundPlugin as H5RefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\RefundPlugin as MiniRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\RefundPlugin as PosRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\RefundPlugin as ScanRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\RefundPlugin as WebRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;

class RefundShortcut implements ShortcutInterface
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
        $action = $params['_action'] ?? AlipayAction::REFUND_DEFAULT;

        return match ($action) {
            AlipayAction::REFUND_DEFAULT => $this->defaultPlugins(),
            AlipayAction::REFUND_AGREEMENT => $this->agreementPlugins(),
            AlipayAction::REFUND_APP => $this->appPlugins(),
            AlipayAction::REFUND_AUTHORIZATION => $this->authorizationPlugins(),
            AlipayAction::REFUND_MINI => $this->miniPlugins(),
            AlipayAction::REFUND_POS => $this->posPlugins(),
            AlipayAction::REFUND_SCAN => $this->scanPlugins(),
            AlipayAction::REFUND_H5 => $this->h5Plugins(),
            AlipayAction::REFUND_WEB => $this->webPlugins(),
            AlipayAction::REFUND_TRANSFER => $this->transferPlugins(),
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
            AgreementRefundPlugin::class,
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
            AppRefundPlugin::class,
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
            AuthorizationRefundPlugin::class,
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
            MiniRefundPlugin::class,
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
            PosRefundPlugin::class,
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
            ScanRefundPlugin::class,
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
            H5RefundPlugin::class,
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
            WebRefundPlugin::class,
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
            FundTransferRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
