<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanFeePlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanNormalPlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanPreAuthPlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanPreOrderPlugin;
use Yansongda\Pay\Plugin\Unipay\Shortcut\ScanShortcut;
use Yansongda\Pay\Tests\TestCase;

class ScanShortcutTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ScanShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            ScanNormalPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testPreAuth()
    {
        self::assertEquals([
            ScanPreAuthPlugin::class,
        ], $this->plugin->getPlugins(['_type' => 'pre_auth']));
    }

    public function testPreOrder()
    {
        self::assertEquals([
            ScanPreOrderPlugin::class,
        ], $this->plugin->getPlugins(['_type' => 'pre_order']));
    }

    public function testFee()
    {
        self::assertEquals([
            ScanFeePlugin::class,
        ], $this->plugin->getPlugins(['_type' => 'fee']));
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_MULTI_TYPE_ERROR);
        self::expectExceptionMessage('Scan type [fooPlugins] not supported');

        $this->plugin->getPlugins(['_type' => 'foo']);
    }
}
