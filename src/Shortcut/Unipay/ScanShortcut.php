<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Unipay;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Unipay\PreparePlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanFeePlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanPlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanPreAuthPlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanPreOrderPlugin;
use Yansongda\Pay\Plugin\Unipay\RadarSignPlugin;
use Yansongda\Supports\Str;

class ScanShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $typeMethod = Str::camel($params['_action'] ?? 'default').'Plugins';

        if (method_exists($this, $typeMethod)) {
            return $this->{$typeMethod}();
        }

        throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "Scan action [{$typeMethod}] not supported");
    }

    protected function defaultPlugins(): array
    {
        return [
            PreparePlugin::class,
            ScanPlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function preAuthPlugins(): array
    {
        return [
            PreparePlugin::class,
            ScanPreAuthPlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function preOrderPlugins(): array
    {
        return [
            PreparePlugin::class,
            ScanPreOrderPlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function feePlugins(): array
    {
        return [
            PreparePlugin::class,
            ScanFeePlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ];
    }
}
