<?php

namespace Yansongda\Pay\Tests\Shortcut\Alipay;

use Yansongda\Pay\Plugin\Alipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\AddSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\FormatBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Web\PayPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Shortcut\Alipay\WebShortcut;
use Yansongda\Pay\Tests\TestCase;

class WebShortcutTest extends TestCase
{
    protected WebShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new WebShortcut();
    }

    public function testNormal()
    {
        $result = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponseHtmlPlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
