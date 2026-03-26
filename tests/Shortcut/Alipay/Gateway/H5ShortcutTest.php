<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay\Gateway;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\Pay\H5\HtmlPayPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\StartPlugin;
use Yansongda\Pay\Shortcut\Alipay\Gateway\H5Shortcut;
use Yansongda\Pay\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class H5ShortcutTest extends TestCase
{
    protected H5Shortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new H5Shortcut();
    }

    public function testNormal(): void
    {
        self::assertEquals([
            StartPlugin::class,
            HtmlPayPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponseHtmlPlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }
}
