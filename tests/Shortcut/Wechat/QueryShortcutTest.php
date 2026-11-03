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
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\QueryByWxPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\QueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\App\QueryPlugin as AppQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\QueryPlugin as CombineQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\QueryPlugin as H5QueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\QueryPlugin as JsapiQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\QueryRefundPlugin as JsapiQueryRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\QueryPlugin as MiniQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\QueryPlugin as NativeQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\QueryShortcut;

describe('query', function () {
    it('默认', function () {
        $plugin = new QueryShortcut();
        expect($plugin->getPlugins([]))->toEqual([
            StartPlugin::class,
            JsapiQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('APP', function () {
        $plugin = new QueryShortcut();
        expect($plugin->getPlugins(['_action' => 'app']))->toEqual([
            StartPlugin::class,
            AppQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('合单', function () {
        $plugin = new QueryShortcut();
        expect($plugin->getPlugins(['_action' => 'combine']))->toEqual([
            StartPlugin::class,
            CombineQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('合单参数', function () {
        $plugin = new QueryShortcut();
        expect($plugin->getPlugins(['combine_out_trade_no' => '123abc']))->toEqual([
            StartPlugin::class,
            CombineQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('H5', function () {
        $plugin = new QueryShortcut();
        expect($plugin->getPlugins(['_action' => 'h5']))->toEqual([
            StartPlugin::class,
            H5QueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('JSAPI', function () {
        $plugin = new QueryShortcut();
        expect($plugin->getPlugins(['_action' => 'jsapi']))->toEqual([
            StartPlugin::class,
            JsapiQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('小程序', function () {
        $plugin = new QueryShortcut();
        expect($plugin->getPlugins(['_action' => 'mini']))->toEqual([
            StartPlugin::class,
            MiniQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('Native', function () {
        $plugin = new QueryShortcut();
        expect($plugin->getPlugins(['_action' => 'native']))->toEqual([
            StartPlugin::class,
            NativeQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });
});

describe('refund', function () {
    it('退款', function () {
        $plugin = new QueryShortcut();
        expect($plugin->getPlugins(['_action' => 'refund']))->toEqual([
            StartPlugin::class,
            JsapiQueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });
});

describe('transfer', function () {
    it('转账', function () {
        $plugin = new QueryShortcut();
        expect($plugin->getPlugins(['_action' => 'transfer']))->toEqual([
            StartPlugin::class,
            QueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);

        expect($plugin->getPlugins(['_action' => 'transfer', 'transfer_bill_no' => '123']))->toEqual([
            StartPlugin::class,
            QueryByWxPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });
});

describe('非法动作', function () {
    it('动作非法', function () {
        $plugin = new QueryShortcut();

        $plugin->getPlugins(['_action' => 'foo']);
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);
});