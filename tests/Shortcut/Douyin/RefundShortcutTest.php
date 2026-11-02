<?php

declare(strict_types=1);

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

it('foo', function () {
    $plugin = new RefundShortcut();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

    $plugin->getPlugins(['_action' => 'foo']);
});

it('default', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins([]))->toBe([
        StartPlugin::class,
        ObtainClientTokenPlugin::class,
        RefundPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('audit', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins(['_action' => 'audit']))->toBe([
        StartPlugin::class,
        ObtainClientTokenPlugin::class,
        AuditPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

