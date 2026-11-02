<?php

declare(strict_types=1);

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

it('默认', function () {
    $plugin = new PayScoreShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        CreatePlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('查询', function () {
    $plugin = new PayScoreShortcut();
    expect($plugin->getPlugins(['_action' => 'query']))->toEqual([
        StartPlugin::class,
        QueryPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('撤销', function () {
    $plugin = new PayScoreShortcut();
    expect($plugin->getPlugins(['_action' => 'cancel']))->toEqual([
        StartPlugin::class,
        CancelPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('完成', function () {
    $plugin = new PayScoreShortcut();
    expect($plugin->getPlugins(['_action' => 'complete']))->toEqual([
        StartPlugin::class,
        CompletePlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('修改', function () {
    $plugin = new PayScoreShortcut();
    expect($plugin->getPlugins(['_action' => 'modify']))->toEqual([
        StartPlugin::class,
        ModifyPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('同步', function () {
    $plugin = new PayScoreShortcut();
    expect($plugin->getPlugins(['_action' => 'sync']))->toEqual([
        StartPlugin::class,
        SyncPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('支付', function () {
    $plugin = new PayScoreShortcut();
    expect($plugin->getPlugins(['_action' => 'pay']))->toEqual([
        StartPlugin::class,
        PayPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('权限', function () {
    $plugin = new PayScoreShortcut();
    expect($plugin->getPlugins(['_action' => 'permissions']))->toEqual([
        StartPlugin::class,
        PermissionsCreatePlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('权限查询', function () {
    $plugin = new PayScoreShortcut();
    expect($plugin->getPlugins(['_action' => 'permissions_query']))->toEqual([
        StartPlugin::class,
        PermissionsQueryPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('权限终止', function () {
    $plugin = new PayScoreShortcut();
    expect($plugin->getPlugins(['_action' => 'permissions_terminate']))->toEqual([
        StartPlugin::class,
        PermissionsTerminatePlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('默认动作', function () {
    $plugin = new PayScoreShortcut();
    expect($plugin->getPlugins(['_action' => 'default']))->toEqual([
        StartPlugin::class,
        CreatePlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('foo', function () {
    $plugin = new PayScoreShortcut();

    $plugin->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

