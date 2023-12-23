<?php

namespace Yansongda\Pay\Tests\Shortcut\Alipay;

use Yansongda\Pay\Plugin\Alipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Mini\PayPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Shortcut\Alipay\MiniShortcut;
use Yansongda\Pay\Tests\TestCase;

class MiniShortcutTest extends TestCase
{
    protected MiniShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new MiniShortcut();
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
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
