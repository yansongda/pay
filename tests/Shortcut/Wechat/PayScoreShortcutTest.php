<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Wechat;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\CancelPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\CompletePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\CreatePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\ModifyPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\PayPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions\CreatePlugin as PermissionsCreatePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions\QueryPlugin as PermissionsQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions\TerminatePlugin as PermissionsTerminatePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\QueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\SyncPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\PayScoreShortcut;
use Yansongda\Pay\Tests\TestCase;

class PayScoreShortcutTest extends TestCase
{
    protected PayScoreShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayScoreShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            CreatePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testQuery()
    {
        self::assertEquals([
            StartPlugin::class,
            QueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'query']));
    }

    public function testCancel()
    {
        self::assertEquals([
            StartPlugin::class,
            CancelPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'cancel']));
    }

    public function testComplete()
    {
        self::assertEquals([
            StartPlugin::class,
            CompletePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'complete']));
    }

    public function testModify()
    {
        self::assertEquals([
            StartPlugin::class,
            ModifyPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'modify']));
    }

    public function testSync()
    {
        self::assertEquals([
            StartPlugin::class,
            SyncPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'sync']));
    }

    public function testPay()
    {
        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'pay']));
    }

    public function testPermissions()
    {
        self::assertEquals([
            StartPlugin::class,
            PermissionsCreatePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'permissions']));
    }

    public function testPermissionsQuery()
    {
        self::assertEquals([
            StartPlugin::class,
            PermissionsQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'permissionsQuery']));
    }

    public function testPermissionsTerminate()
    {
        self::assertEquals([
            StartPlugin::class,
            PermissionsTerminatePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'permissionsTerminate']));
    }

    public function testDefaultAction()
    {
        self::assertEquals([
            StartPlugin::class,
            CreatePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'default']));
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

        $this->plugin->getPlugins(['_action' => 'foo']);
    }
}
