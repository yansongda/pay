<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Unipay;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanFeePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPreAuthPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPreOrderPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\VerifySignaturePlugin;
use Yansongda\Supports\Str;

class ScanShortcut implements ShortcutInterface
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
            ScanPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function preAuthPlugins(): array
    {
        return [
            StartPlugin::class,
            ScanPreAuthPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function preOrderPlugins(): array
    {
        return [
            StartPlugin::class,
            ScanPreOrderPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function feePlugins(): array
    {
        return [
            StartPlugin::class,
            ScanFeePlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }
}
