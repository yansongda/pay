<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay\V3;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\Scan\PayPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\StartPlugin;
use Yansongda\Pay\Shortcut\Alipay\V3\ScanShortcut;
use Yansongda\Pay\Tests\TestCase;

class ScanShortcutTest extends TestCase
{
    protected ScanShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new ScanShortcut();
    }

    public function testNormal()
    {
        $result = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
