<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Douyin;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Action\DouyinAction;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ObtainClientTokenPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryCpsPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\QueryPlugin as RefundQueryPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ResponsePlugin;

/**
 * 抖音查询：`_action` 分发 `order`（默认，order_query）/`cps`（query_cps）/`refund`（refund_query）.
 */
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
        $action = DouyinAction::tryFrom($params['_action'] ?? DouyinAction::Default->value)
            ?? throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, '不支持的 _action ['.($params['_action'] ?? '').']');

        return match ($action) {
            DouyinAction::Default, DouyinAction::Order => $this->orderPlugins(),
            DouyinAction::Cps => $this->cpsPlugins(),
            DouyinAction::Refund => $this->refundPlugins(),
            default => throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            ),
        };
    }

    /**
     * @return array<class-string>
     */
    protected function defaultPlugins(): array
    {
        return [
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
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
    protected function orderPlugins(): array
    {
        return $this->defaultPlugins();
    }

    /**
     * @return array<class-string>
     */
    protected function cpsPlugins(): array
    {
        return [
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            QueryCpsPlugin::class,
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
            ObtainClientTokenPlugin::class,
            RefundQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
