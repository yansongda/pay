<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Paypal;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Paypal\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\CapturePlugin;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\PayPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ResponsePlugin;

class WebShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $action = $params['_action'] ?? 'default';

        return match ($action) {
            'default', 'pay' => $this->payPlugins(),
            'capture' => $this->capturePlugins(),
            default => throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "不支持的 PayPal Web 操作: [{$action}]"),
        };
    }

    protected function payPlugins(): array
    {
        return [
            StartPlugin::class,
            ObtainAccessTokenPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function capturePlugins(): array
    {
        return [
            StartPlugin::class,
            ObtainAccessTokenPlugin::class,
            CapturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
