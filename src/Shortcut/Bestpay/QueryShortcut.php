<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Bestpay;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Action\BestpayAction;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Bestpay\V1\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\Pay\AggregateQueryPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\ResponsePlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\VerifySignaturePlugin;

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
        $action = $params['_action'] ?? BestpayAction::QUERY_DEFAULT;

        return match ($action) {
            BestpayAction::QUERY_DEFAULT => $this->orderQueryPlugins(),
            BestpayAction::QUERY_AGGREGATE => $this->aggregateQueryPlugins(),
            default => throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            ),
        };
    }

    /**
     * 超级收银台（1008）/integrate/orderQuery.
     *
     * @return array<class-string>
     */
    protected function orderQueryPlugins(): array
    {
        return [
            StartPlugin::class,
            QueryPlugin::class,
            AddPayloadSignPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * 线下聚合（1006）/aggregate/aggregatepay/tradeQuery.
     *
     * @return array<class-string>
     */
    protected function aggregateQueryPlugins(): array
    {
        return [
            StartPlugin::class,
            AggregateQueryPlugin::class,
            AddPayloadSignPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
