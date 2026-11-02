<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Open\Authorization\TokenAppPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Open\Authorization\TokenAppQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\AuthShortcut;

it('默认 app 的 token', function () {
    $shortcut = new AuthShortcut();
    $result = $shortcut->getPlugins([]);

    expect($result)->toEqual([
        StartPlugin::class,
        TokenAppPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('显式指定 app 的 token', function () {
    $shortcut = new AuthShortcut();
    $result = $shortcut->getPlugins(['_action' => 'token_app']);

    expect($result)->toEqual([
        StartPlugin::class,
        TokenAppPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('查询', function () {
    $shortcut = new AuthShortcut();
    $result = $shortcut->getPlugins(['_action' => 'query']);

    expect($result)->toEqual([
        StartPlugin::class,
        TokenAppQueryPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('foo 参数', function () {
    $shortcut = new AuthShortcut();

    $shortcut->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

