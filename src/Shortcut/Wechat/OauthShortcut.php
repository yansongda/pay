<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Wechat;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\Code2SessionPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\RefreshTokenPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\UserInfoPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\WebAccessTokenPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\ResponsePlugin;
use Yansongda\Supports\Str;

class OauthShortcut implements ShortcutInterface
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
    protected function webTokenPlugins(): array
    {
        return [
            StartPlugin::class,
            WebAccessTokenPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function refreshPlugins(): array
    {
        return [
            StartPlugin::class,
            RefreshTokenPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function userinfoPlugins(): array
    {
        return [
            StartPlugin::class,
            UserInfoPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function sessionPlugins(): array
    {
        return [
            StartPlugin::class,
            Code2SessionPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
