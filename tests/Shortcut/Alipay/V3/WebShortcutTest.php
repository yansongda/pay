<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay\V3;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\Pay\Web\HtmlPayPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Alipay\Gateway\StartPlugin;
use Yansongda\Pay\Shortcut\Alipay\V3\WebShortcut;
use Yansongda\Pay\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class WebShortcutTest extends TestCase
{
    protected WebShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new WebShortcut();
    }

    public function testNormal(): void
    {
        $result = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            HtmlPayPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponseHtmlPlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
