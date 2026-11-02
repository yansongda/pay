<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\ApplyPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\ContractOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\MiniOnlyContractPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\App\InvokePlugin as AppInvokePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\Mini\InvokePlugin as MiniInvokePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\Mp\InvokePlugin as MpInvokePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\PapayShortcut;

it('默认', function () {
    $plugin = new PapayShortcut();
    expect($plugin->getPlugins(['_type' => 'mini']))->toEqual([
        StartPlugin::class,
        ContractOrderPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        MiniInvokePlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);

    expect($plugin->getPlugins(['_type' => 'app']))->toEqual([
        StartPlugin::class,
        ContractOrderPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        AppInvokePlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);

    expect($plugin->getPlugins(['_type' => 'mp']))->toEqual([
        StartPlugin::class,
        ContractOrderPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        MpInvokePlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);

    expect($plugin->getPlugins(['_type' => 'scan']))->toEqual([
        StartPlugin::class,
        ContractOrderPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);

    expect($plugin->getPlugins(['_type' => 'h5']))->toEqual([
        StartPlugin::class,
        ContractOrderPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);

    $plugin->getPlugins(['_type' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_WECHAT_PAPAY_TYPE_NOT_SUPPORTED);

it('订单', function () {
    $plugin = new PapayShortcut();
    expect($plugin->getPlugins(['_action' => 'order', '_type' => 'mini']))->toEqual([
        StartPlugin::class,
        ContractOrderPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        MiniInvokePlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);

    expect($plugin->getPlugins(['_action' => 'order', '_type' => 'app']))->toEqual([
        StartPlugin::class,
        ContractOrderPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        AppInvokePlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);

    expect($plugin->getPlugins(['_action' => 'order', '_type' => 'scan']))->toEqual([
        StartPlugin::class,
        ContractOrderPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);

    expect($plugin->getPlugins(['_action' => 'order', '_type' => 'h5']))->toEqual([
        StartPlugin::class,
        ContractOrderPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('申请', function () {
    $plugin = new PapayShortcut();
    expect($plugin->getPlugins(['_action' => 'apply']))->toEqual([
        StartPlugin::class,
        ApplyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('合约', function () {
    $plugin = new PapayShortcut();
    expect($plugin->getPlugins(['_action' => 'contract', '_type' => 'mini']))->toEqual([
        StartPlugin::class,
        MiniOnlyContractPlugin::class,
        AddPayloadSignaturePlugin::class,
    ]);

    $plugin->getPlugins(['_action' => 'contract', '_type' => 'app']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_WECHAT_PAPAY_TYPE_NOT_SUPPORTED);

it('foo', function () {
    $plugin = new PapayShortcut();

    $plugin->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

