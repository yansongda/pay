<?php

namespace Yansongda\Pay\Tests\Shortcut\Alipay;

use Yansongda\Pay\Plugin\Alipay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\App\PayPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponseInvokeStringPlugin;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Shortcut\Alipay\AppShortcut;
use Yansongda\Pay\Tests\TestCase;

class AppShortcutTest extends TestCase
{
    protected AppShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new AppShortcut();
    }

    public function testNormal()
    {
        $result = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            ResponseInvokeStringPlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
