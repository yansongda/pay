<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Wechat;

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
use Yansongda\Pay\Shortcut\Wechat\OauthShortcut;
use Yansongda\Pay\Tests\TestCase;

class OauthShortcutTest extends TestCase
{
    protected OauthShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new OauthShortcut();
    }

    public function testWebToken(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'web_token', 'code' => 'mock-code']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(WebAccessTokenPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[2]);
        self::assertSame(ResponsePlugin::class, $plugins[3]);
        self::assertSame(ParserPlugin::class, $plugins[4]);
    }

    public function testRefresh(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'refresh', 'refresh_token' => 'mock-refresh-token']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(RefreshTokenPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[2]);
        self::assertSame(ResponsePlugin::class, $plugins[3]);
        self::assertSame(ParserPlugin::class, $plugins[4]);
    }

    public function testUserinfo(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'userinfo', 'access_token' => 'mock-access-token', 'openid' => 'mock-openid']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(UserInfoPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[2]);
        self::assertSame(ResponsePlugin::class, $plugins[3]);
        self::assertSame(ParserPlugin::class, $plugins[4]);
    }

    public function testSession(): void
    {
        $plugins = $this->plugin->getPlugins(['_action' => 'session', 'js_code' => 'mock-js-code']);

        self::assertIsArray($plugins);
        self::assertSame(StartPlugin::class, $plugins[0]);
        self::assertSame(Code2SessionPlugin::class, $plugins[1]);
        self::assertSame(AddRadarPlugin::class, $plugins[2]);
        self::assertSame(ResponsePlugin::class, $plugins[3]);
        self::assertSame(ParserPlugin::class, $plugins[4]);
    }

    public function testInvalidActionThrows(): void
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

        $this->plugin->getPlugins(['_action' => 'invalid_action']);
    }
}
