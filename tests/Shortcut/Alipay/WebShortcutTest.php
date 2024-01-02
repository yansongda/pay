<?php

namespace Yansongda\Pay\Tests\Shortcut\Alipay;

use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\PayPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
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
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponseHtmlPlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
