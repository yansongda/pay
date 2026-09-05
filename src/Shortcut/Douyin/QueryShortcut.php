<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Douyin;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ObtainClientTokenPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryCpsPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ResponsePlugin;
use Yansongda\Supports\Str;

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
        $method = Str::camel($params['_action'] ?? 'default').'Plugins';

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "您所提供的 action 方法 [{$method}] 不支持，请参考文档或源码确认");
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
            QueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
