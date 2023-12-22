<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Alipay;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Alipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\AddSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\FormatBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Fund\Transfer\RefundPlugin as FundTransferRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Agreement\RefundPlugin as AgreementRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\App\RefundPlugin as AppRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Authorization\RefundPlugin as AuthorizationRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Mini\RefundPlugin as MiniRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Pos\RefundPlugin as PosRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Scan\RefundPlugin as ScanRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Wap\RefundPlugin as WapRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Web\RefundPlugin as WebRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Supports\Str;

class RefundShortcut implements ShortcutInterface
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

        throw new InvalidParamsException(Exception::SHORTCUT_MULTI_ACTION_INVALID, "Refund action [{$method}] not supported");
    }

    protected function defaultPlugins(): array
    {
        return [
            StartPlugin::class,
            WebRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            AgreementRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            AppRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            AuthorizationRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            MiniRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            PosRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            ScanRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            WapRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            WebRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            FundTransferRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
