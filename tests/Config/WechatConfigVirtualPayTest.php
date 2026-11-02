<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\WechatConfigVirtualPay;
use Yansongda\Pay\Enum\Wechat\VirtualPayEnv;
use Yansongda\Pay\Exception\Exception;

it('default values', function () {
    $vp = new WechatConfigVirtualPay();

    expect($vp->getAppKey())->toBeNull();
    expect($vp->getSandboxAppKey())->toBeNull();
    expect($vp->getOfferId())->toBeNull();
});

it('set and get app key', function () {
    $vp = new WechatConfigVirtualPay();
    $vp->setAppKey('prod-key');

    expect($vp->getAppKey())->toBe('prod-key');
});

it('set and get app secret', function () {
    $vp = new WechatConfigVirtualPay();

    expect($vp->getAppSecret())->toBeNull();

    $vp->setAppSecret('secret');

    expect($vp->getAppSecret())->toBe('secret');
});

it('access token cache fields', function () {
    $vp = new WechatConfigVirtualPay();

    expect($vp->getAccessToken())->toBeNull();
    expect($vp->getAccessTokenExpiry())->toBeNull();

    $vp->setAccessToken('token');
    $vp->setAccessTokenExpiry(123);

    expect($vp->getAccessToken())->toBe('token');
    expect($vp->getAccessTokenExpiry())->toBe(123);
});

it('set and get sandbox app key', function () {
    $vp = new WechatConfigVirtualPay();
    $vp->setSandboxAppKey('sandbox-key');

    expect($vp->getSandboxAppKey())->toBe('sandbox-key');
});

it('set and get offer id', function () {
    $vp = new WechatConfigVirtualPay();
    $vp->setOfferId('offer-123');

    expect($vp->getOfferId())->toBe('offer-123');
});

it('get app key without env returns app key', function () {
    $vp = new WechatConfigVirtualPay();
    $vp->setAppKey('prod-key');

    expect($vp->getAppKey(0))->toBe('prod-key');
});

it('get app key with virtual pay env production returns app key', function () {
    $vp = new WechatConfigVirtualPay();
    $vp->setAppKey('prod-key');
    $vp->setSandboxAppKey('sandbox-key');

    expect($vp->getAppKey(VirtualPayEnv::PRODUCTION))->toBe('prod-key');
});

it('get app key with virtual pay env sandbox returns sandbox key', function () {
    $vp = new WechatConfigVirtualPay();
    $vp->setAppKey('prod-key');
    $vp->setSandboxAppKey('sandbox-key');

    expect($vp->getAppKey(VirtualPayEnv::SANDBOX))->toBe('sandbox-key');
});

it('get app key with env one returns sandbox when configured', function () {
    $vp = new WechatConfigVirtualPay();
    $vp->setAppKey('prod-key');
    $vp->setSandboxAppKey('sandbox-key');

    expect($vp->getAppKey(1))->toBe('sandbox-key');
});

it('get app key with env one throws when no sandbox key', function () {
    $vp = new WechatConfigVirtualPay();
    $vp->setAppKey('prod-key');

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_WECHAT_INVALID);

    $vp->getAppKey(1);
});

it('get app key with env one throws when neither configured', function () {
    $vp = new WechatConfigVirtualPay();

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_WECHAT_INVALID);

    $vp->getAppKey(1);
});

