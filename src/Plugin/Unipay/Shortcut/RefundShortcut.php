<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\RefundPlugin;
use Yansongda\Supports\Str;

class RefundShortcut implements ShortcutInterface
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

        throw new InvalidParamsException(Exception::SHORTCUT_MULTI_TYPE_ERROR, "Refund type [$typeMethod] not supported");
    }

    public function defaultPlugins(): array
    {
        return [
            RefundPlugin::class,
        ];
    }

    public function qrCodePlugins(): array
    {
        return [
            \Yansongda\Pay\Plugin\Unipay\QrCode\RefundPlugin::class,
        ];
    }
}
