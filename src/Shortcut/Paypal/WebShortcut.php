<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Paypal;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Action\PaypalAction;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Paypal\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\CapturePlugin;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\PayPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ResponsePlugin;

class WebShortcut implements ShortcutInterface
{
    /**
     * @param array<string, mixed> $params
     *
     * @return array<class-string>
     *
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $action = PaypalAction::tryFrom($params['_action'] ?? PaypalAction::Default->value)
            ?? throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, '不支持的 _action ['.($params['_action'] ?? '').']');

        return match ($action) {
            PaypalAction::Default, PaypalAction::Pay => $this->payPlugins(),
            PaypalAction::Capture => $this->capturePlugins(),
            default => throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            ),
        };
    }

    /**
     * @return array<class-string>
     */
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

    /**
     * @return array<class-string>
     */
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
