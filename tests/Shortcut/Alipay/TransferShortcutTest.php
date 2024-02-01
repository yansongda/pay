<?php

namespace Yansongda\Pay\Tests\Shortcut\Alipay;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Fund\TransferPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\TransferShortcut;
use Yansongda\Pay\Tests\TestCase;

class TransferShortcutTest extends TestCase
{
    protected TransferShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new TransferShortcut();
    }

    public function testNormal()
    {
        $result = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            TransferPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
