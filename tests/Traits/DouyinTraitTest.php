<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Support\Traits\DouyinHelpers;
use Yansongda\Pay\Tests\Stubs\DouyinTraitStub;

uses(DouyinHelpers::class);

describe('getDouyinUrl', function () {
    it('正常', function () {
        $normalConfig = new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
            'app_private_key' => 'douyin_app_private_key',
            'douyin_public_key' => 'douyin_platform_public_key',
        ]);
        $serviceConfig = new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
            'app_private_key' => 'douyin_app_private_key',
            'douyin_public_key' => 'douyin_platform_public_key',
            'mode' => PayMode::SERVICE,
        ]);

        expect(DouyinTraitStub::getDouyinUrl($normalConfig, new Collection(['_url' => 'https://yansongda.cn'])))->toEqual('https://yansongda.cn');
        expect(DouyinTraitStub::getDouyinUrl($normalConfig, new Collection(['_url' => '/api/v1/yansongda'])))->toEqual('https://open.douyin.com/api/v1/yansongda');
        expect(DouyinTraitStub::getDouyinUrl($serviceConfig, new Collection(['_service_url' => '/api/v1/service/yansongda'])))->toEqual('https://open.douyin.com/api/v1/service/yansongda');
        expect(DouyinTraitStub::getDouyinUrl($serviceConfig, new Collection(['_url' => '/foo', '_service_url' => '/api/v1/service/yansongda'])))->toEqual('https://open.douyin.com/api/v1/service/yansongda');

        DouyinTraitStub::getDouyinUrl($normalConfig, new Collection([]));
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_DOUYIN_URL_MISSING);
});

describe('getDouyinTradeSign', function () {
    it('正常', function () {
        $config = $this->getDouyinConfig();
        $timestamp = '1700000000';
        $nonce = 'abcdef1234567890';
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $header = DouyinTraitStub::getDouyinTradeSign($config, 'POST', '/api/v1/trade/create', $body, $timestamp, $nonce);

        expect($header)->toBe('SHA256-RSA2048 appid="tt226e54d3bd581bf801",nonce_str="abcdef1234567890",timestamp="1700000000",key_version=1,signature="'
            .base64_encode($this->signDouyinContents("POST\n/api/v1/trade/create\n1700000000\nabcdef1234567890\n".$body."\n", file_get_contents(cert_path('douyinAppPrivateKey.pem'))))
            .'"');
    });

    it('签名往返', function () {
        $config = $this->getDouyinConfig();
        $timestamp = '1700000000';
        $nonce = 'abcdef1234567890';
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $header = DouyinTraitStub::getDouyinTradeSign($config, 'POST', '/api/v1/trade/create', $body, $timestamp, $nonce);

        preg_match('/signature="([^"]+)"$/', $header, $matches);
        expect($matches)->not->toBeEmpty();

        $publicKey = openssl_pkey_get_public(file_get_contents(cert_path('douyinAppPublicKey.pem')));
        expect($publicKey)->not->toBeFalse();

        expect(openssl_verify("POST\n/api/v1/trade/create\n1700000000\nabcdef1234567890\n".$body."\n", base64_decode($matches[1]), $publicKey, OPENSSL_ALGO_SHA256))->toBe(1);
    });

    it('私钥非法', function () {
        $config = new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
            'app_private_key' => 'invalid_private_key',
        ]);

        DouyinTraitStub::getDouyinTradeSign($config, 'POST', '/api/v1/trade/create', '{}', '1700000000', 'abcdef1234567890');
    })->throws(InvalidConfigException::class, null, Exception::CONFIG_DOUYIN_INVALID);
});

describe('verifyDouyinTradeSign', function () {
    it('正常', function () {
        $config = $this->getDouyinConfig();
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $request = $this->getDouyinCallbackRequest($body);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);

        expect(true)->toBeTrue();
    });

    it('body 被篡改', function () {
        $config = $this->getDouyinConfig();

        // 用原始 body 签名，但请求体被篡改
        $request = $this->getDouyinCallbackRequest('{"out_trade_no":"yansongda","total_amount":100}', [], '{"out_trade_no":"yansongda","total_amount":1}');

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

    it('签名被篡改', function () {
        $config = $this->getDouyinConfig();
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $request = $this->getDouyinCallbackRequest($body, ['Byte-Signature' => base64_encode('tampered-signature')]);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

    it('时间戳为空', function () {
        $config = $this->getDouyinConfig();
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $request = $this->getDouyinCallbackRequest($body, ['Byte-Timestamp' => '']);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    })->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

    it('随机串为空', function () {
        $config = $this->getDouyinConfig();
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $request = $this->getDouyinCallbackRequest($body, ['Byte-Nonce-Str' => '']);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    })->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

    it('签名为空', function () {
        $config = $this->getDouyinConfig();
        $body = '{"out_trade_no":"yansongda","total_amount":1}';

        $request = $this->getDouyinCallbackRequest($body, ['Byte-Signature' => '']);

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    })->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

    it('body 为空', function () {
        $config = $this->getDouyinConfig();

        $request = $this->getDouyinCallbackRequest('');

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    })->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

    it('平台公钥无效', function () {
        $config = new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
            'douyin_public_key' => 'invalid_public_key',
        ]);

        $request = $this->getDouyinCallbackRequest('{"out_trade_no":"yansongda","total_amount":1}');

        DouyinTraitStub::verifyDouyinTradeSign($request, $config);
    })->throws(InvalidConfigException::class, null, Exception::CONFIG_DOUYIN_INVALID);
});