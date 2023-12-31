<?php

namespace Yansongda\Pay\Tests\Shortcut\Alipay;

use Yansongda\Pay\Plugin\Alipay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\H5\PayPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Shortcut\Alipay\H5Shortcut;
use Yansongda\Pay\Tests\TestCase;

class H5ShortcutTest extends TestCase
{
    protected H5Shortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new H5Shortcut();
    }

    public function testNormal()
    {
        $result = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            ResponseHtmlPlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
