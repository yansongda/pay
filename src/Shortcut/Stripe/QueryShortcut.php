<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Stripe;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Action\StripeAction;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;

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
        $action = StripeAction::tryFrom($params['_action'] ?? StripeAction::Default->value)
            ?? throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, '不支持的 _action ['.($params['_action'] ?? '').']');

        return match ($action) {
            StripeAction::Default, StripeAction::Order => $this->orderPlugins(),
            StripeAction::Refund => $this->refundPlugins(),
        };
    }

    /**
     * @return array<class-string>
     */
    protected function orderPlugins(): array
    {
        return [
            StartPlugin::class,
            QueryPlugin::class,
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
            QueryRefundPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
