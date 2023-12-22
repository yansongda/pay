<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Unipay\PreparePlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\PosNormalPlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\PosPreAuthPlugin;
use Yansongda\Pay\Plugin\Unipay\RadarSignPlugin;
use Yansongda\Pay\Plugin\Unipay\Shortcut\PosShortcut;
use Yansongda\Pay\Tests\TestCase;

class PosShortcutTest extends TestCase
{
    protected PosShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PosShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            PreparePlugin::class,
            PosNormalPlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testPreAuth()
    {
        self::assertEquals([
            PreparePlugin::class,
            PosPreAuthPlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'pre_auth']));
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_MULTI_ACTION_INVALID);
        self::expectExceptionMessage('Pos action [fooPlugins] not supported');

        $this->plugin->getPlugins(['_action' => 'foo']);
    }
}
