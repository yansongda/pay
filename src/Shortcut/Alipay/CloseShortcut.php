<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Alipay;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\ClosePlugin as AgreementClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\App\ClosePlugin as AppClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\ClosePlugin as AuthorizationClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\ClosePlugin as WapClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\ClosePlugin as MiniClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\ClosePlugin as PosClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\ClosePlugin as ScanClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\ClosePlugin as WebClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Supports\Str;

class CloseShortcut implements ShortcutInterface
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

        throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "Close action [{$method}] not supported");
    }

    protected function defaultPlugins(): array
    {
        return $this->webPlugins();
    }

    protected function agreementPlugins(): array
    {
        return [
            StartPlugin::class,
            AgreementClosePlugin::class,
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
            AppClosePlugin::class,
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
            AuthorizationClosePlugin::class,
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
            MiniClosePlugin::class,
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
            PosClosePlugin::class,
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
            ScanClosePlugin::class,
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
            WapClosePlugin::class,
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
            WebClosePlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
