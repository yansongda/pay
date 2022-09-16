<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanFeePlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanNormalPlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanPreAuthPlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanPreOrderPlugin;
use Yansongda\Supports\Str;

class ScanShortcut implements ShortcutInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $typeMethod = Str::camel($params['_type'] ?? 'default').'Plugins';

        if (method_exists($this, $typeMethod)) {
            return $this->{$typeMethod}();
        }

        throw new InvalidParamsException(Exception::SHORTCUT_MULTI_TYPE_ERROR, "Scan type [$typeMethod] not supported");
    }

    public function defaultPlugins(): array
    {
        return [
            ScanNormalPlugin::class,
        ];
    }

    public function preAuthPlugins(): array
    {
        return [
            ScanPreAuthPlugin::class,
        ];
    }

    public function preOrderPlugins(): array
    {
        return [
            ScanPreOrderPlugin::class,
        ];
    }

    public function feePlugins(): array
    {
        return [
            ScanFeePlugin::class,
        ];
    }
}
