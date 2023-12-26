<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Wechat;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\AddPayloadBodyPlugin;
use Yansongda\Pay\Plugin\Wechat\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\App\RefundPlugin as AppRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Combine\RefundPlugin as CombineRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\H5\RefundPlugin as H5RefundPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Jsapi\RefundPlugin as JsapiRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Mini\RefundPlugin as MiniRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Native\RefundPlugin as NativeRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\StartPlugin;
use Yansongda\Pay\Plugin\Wechat\VerifySignaturePlugin;
use Yansongda\Supports\Str;

class RefundShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $action = Str::camel($params['_action'] ?? 'default').'Plugins';

        if (method_exists($this, $action)) {
            return $this->{$action}();
        }

        throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "Refund action [{$action}] not supported");
    }

    protected function defaultPlugins(): array
    {
        return $this->jsapiPlugins();
    }

    protected function appPlugins(): array
    {
        return [
            StartPlugin::class,
            AppRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function combinePlugins(): array
    {
        return [
            StartPlugin::class,
            CombineRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function h5Plugins(): array
    {
        return [
            StartPlugin::class,
            H5RefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function jsapiPlugins(): array
    {
        return [
            StartPlugin::class,
            JsapiRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function miniPlugins(): array
    {
        return [
            StartPlugin::class,
            MiniRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function nativePlugins(): array
    {
        return [
            StartPlugin::class,
            NativeRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }
}
