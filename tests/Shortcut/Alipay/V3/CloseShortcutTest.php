<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay\V3;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\StartPlugin;
use Yansongda\Pay\Shortcut\Alipay\V3\CloseShortcut;
use Yansongda\Pay\Tests\TestCase;

class CloseShortcutTest extends TestCase
{
    protected CloseShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new CloseShortcut();
    }

    public function testNormal()
    {
        $result = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            ClosePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
