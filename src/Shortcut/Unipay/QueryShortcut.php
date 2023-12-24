<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Unipay;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\QueryPlugin;
use Yansongda\Pay\Plugin\Unipay\PreparePlugin;
use Yansongda\Pay\Plugin\Unipay\RadarSignPlugin;
use Yansongda\Supports\Str;

class QueryShortcut implements ShortcutInterface
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

        throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "Query action [{$typeMethod}] not supported");
    }

    protected function defaultPlugins(): array
    {
        return [
            PreparePlugin::class,
            QueryPlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function qrCodePlugins(): array
    {
        return [
            PreparePlugin::class,
            \Yansongda\Pay\Plugin\Unipay\QrCode\QueryPlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ];
    }
}