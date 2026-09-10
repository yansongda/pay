<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Wechat;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Action\WechatAction;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\Code2SessionPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\RefreshTokenPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\UserInfoPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\WebAccessTokenPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\ResponsePlugin;

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
        $action = WechatAction::tryFrom($params['_action'] ?? '')
            ?? throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            );

        return match ($action) {
            WechatAction::WebToken => $this->webTokenPlugins(),
            WechatAction::Refresh => $this->refreshPlugins(),
            WechatAction::Userinfo => $this->userinfoPlugins(),
            WechatAction::Session => $this->sessionPlugins(),
            default => throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            ),
        };
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
