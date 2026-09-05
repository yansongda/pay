<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Douyin;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ObtainClientTokenPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\AuditPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\RefundPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Douyin\RefundShortcut;
use Yansongda\Pay\Tests\TestCase;

class RefundShortcutTest extends TestCase
{
    protected RefundShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundShortcut();
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

        $this->plugin->getPlugins(['_action' => 'foo']);
    }

    public function testDefault()
    {
        self::assertSame([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            RefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testAudit()
    {
        self::assertSame([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            AuditPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'audit']));
    }
}
