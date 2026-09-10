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
use Yansongda\Pay\Plugin\Paypal\V2\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ResponsePlugin;

class QueryShortcut implements ShortcutInterface
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
        $action = $params['_action'] ?? PaypalAction::QUERY_DEFAULT;

        return match ($action) {
            PaypalAction::QUERY_DEFAULT, PaypalAction::QUERY_ORDER => $this->orderPlugins(),
            PaypalAction::QUERY_REFUND => $this->refundPlugins(),
            default => throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            ),
        };
    }

    /**
     * @return array<class-string>
     */
    protected function orderPlugins(): array
    {
        return [
            StartPlugin::class,
            ObtainAccessTokenPlugin::class,
            QueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function refundPlugins(): array
    {
        return [
            StartPlugin::class,
            ObtainAccessTokenPlugin::class,
            QueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
