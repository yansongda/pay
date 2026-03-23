<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Stripe;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;

class QueryShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $action = $params['_action'] ?? 'default';

        return match ($action) {
            'default', 'order' => $this->orderPlugins(),
            'refund' => $this->refundPlugins(),
            default => throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "不支持的 Stripe Query 操作: [{$action}]"),
        };
    }

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
