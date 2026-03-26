<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay\Gateway;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\Pay\App\InvokePayPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\ResponseInvokeStringPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\StartPlugin;
use Yansongda\Pay\Shortcut\Alipay\Gateway\AppShortcut;
use Yansongda\Pay\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class AppShortcutTest extends TestCase
{
    protected AppShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new AppShortcut();
    }

    public function testNormal(): void
    {
        self::assertEquals([
            StartPlugin::class,
            InvokePayPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            ResponseInvokeStringPlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }
}
