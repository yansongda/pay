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

it('token app default', function () {
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

it('token app explicit', function () {
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

it('query', function () {
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

it('foo param', function () {
    $shortcut = new AuthShortcut();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

    $shortcut->getPlugins(['_action' => 'foo']);
});

