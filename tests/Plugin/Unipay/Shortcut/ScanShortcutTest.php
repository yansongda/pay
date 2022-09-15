<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Unipay\QrCode\ScanNormalPlugin;
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

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_MULTI_TYPE_ERROR);
        self::expectExceptionMessage('Scan type [FooPlugins] not supported');

        $this->plugin->getPlugins(['_type' => 'foo']);
    }
}
