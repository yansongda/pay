<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Unipay\QrCode\PosNormalPlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\PosPreAuthPlugin;
use Yansongda\Pay\Plugin\Unipay\Shortcut\PosShortcut;
use Yansongda\Pay\Tests\TestCase;

class PosShortcutTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PosShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            PosNormalPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testPreAuth()
    {
        self::assertEquals([
            PosPreAuthPlugin::class,
        ], $this->plugin->getPlugins(['_type' => 'pre_auth']));
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_MULTI_TYPE_ERROR);
        self::expectExceptionMessage('Pos type [fooPlugins] not supported');

        $this->plugin->getPlugins(['_type' => 'foo']);
    }
}
