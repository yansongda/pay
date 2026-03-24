<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Douyin;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ObtainClientTokenPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Pay\QueryCpsPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Pay\QueryPlugin as TradeQueryPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Refund\QueryRefundPlugin as TradeQueryRefundPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ResponsePlugin;
use Yansongda\Supports\Str;

class TradeQueryShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $method = Str::camel($params['_action'] ?? 'default').'Plugins';

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "您所提供的 action 方法 [{$method}] 不支持，请参考文档或源码确认");
    }

    protected function defaultPlugins(): array
    {
        return $this->orderPlugins();
    }

    protected function orderPlugins(): array
    {
        return [
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            TradeQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

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

    protected function refundPlugins(): array
    {
        return [
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            TradeQueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
