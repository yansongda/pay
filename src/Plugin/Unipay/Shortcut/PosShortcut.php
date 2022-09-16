<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Unipay\QrCode\PosNormalPlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\PosPreAuthPlugin;
use Yansongda\Supports\Str;

class PosShortcut implements ShortcutInterface
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

        throw new InvalidParamsException(Exception::SHORTCUT_MULTI_TYPE_ERROR, "Pos type [$typeMethod] not supported");
    }

    public function defaultPlugins(): array
    {
        return [
            PosNormalPlugin::class,
        ];
    }

    public function preAuthPlugins(): array
    {
        return [
            PosPreAuthPlugin::class,
        ];
    }
}
