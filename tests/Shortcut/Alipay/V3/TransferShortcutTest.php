<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay\V3;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\Fund\Transfer\Fund\TransferPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\V3\TransferShortcut;
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
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
