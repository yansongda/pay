<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Apple;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Action\AppleAction;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\AddRadarPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\QueryHistoryPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\QuerySubscriptionsPlugin;
use Yansongda\Pay\Plugin\Apple\ResponsePlugin;

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
        $action = $params['_action'] ?? AppleAction::QUERY_TRANSACTION;

        return match ($action) {
            AppleAction::QUERY_TRANSACTION => $this->queryPlugins(QueryPlugin::class),
            AppleAction::QUERY_HISTORY => $this->queryPlugins(QueryHistoryPlugin::class),
            AppleAction::QUERY_SUBSCRIPTIONS => $this->queryPlugins(QuerySubscriptionsPlugin::class),
            default => throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            ),
        };
    }

    /**
     * @return array<class-string>
     */
    protected function queryPlugins(string $plugin): array
    {
        return [
            StartPlugin::class,
            $plugin,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
