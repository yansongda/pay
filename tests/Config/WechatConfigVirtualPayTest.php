<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\WechatConfigVirtualPay;
use Yansongda\Pay\Enum\Wechat\VirtualPayEnv;
use Yansongda\Pay\Exception\Exception;

describe('默认值', function () {
    it('默认值', function () {
        $vp = new WechatConfigVirtualPay();

        expect($vp->getAppKey())->toBeNull();
        expect($vp->getSandboxAppKey())->toBeNull();
        expect($vp->getOfferId())->toBeNull();
    });
});

describe('getter/setter', function () {
    it('app_key', function () {
        $vp = new WechatConfigVirtualPay();
        $vp->setAppKey('prod-key');

        expect($vp->getAppKey())->toBe('prod-key');
    });

    it('app_secret', function () {
        $vp = new WechatConfigVirtualPay();

        expect($vp->getAppSecret())->toBeNull();

        $vp->setAppSecret('secret');

        expect($vp->getAppSecret())->toBe('secret');
    });

    it('access token 缓存字段', function () {
        $vp = new WechatConfigVirtualPay();

        expect($vp->getAccessToken())->toBeNull();
        expect($vp->getAccessTokenExpiry())->toBeNull();

        $vp->setAccessToken('token');
        $vp->setAccessTokenExpiry(123);

        expect($vp->getAccessToken())->toBe('token');
        expect($vp->getAccessTokenExpiry())->toBe(123);
    });

    it('沙箱 app_key', function () {
        $vp = new WechatConfigVirtualPay();
        $vp->setSandboxAppKey('sandbox-key');

        expect($vp->getSandboxAppKey())->toBe('sandbox-key');
    });

    it('offer_id', function () {
        $vp = new WechatConfigVirtualPay();
        $vp->setOfferId('offer-123');

        expect($vp->getOfferId())->toBe('offer-123');
    });
});

describe('app_key 环境切换', function () {
    it('未指定环境返回 app_key', function () {
        $vp = new WechatConfigVirtualPay();
        $vp->setAppKey('prod-key');

        expect($vp->getAppKey(0))->toBe('prod-key');
    });

    it('生产环境返回 app_key', function () {
        $vp = new WechatConfigVirtualPay();
        $vp->setAppKey('prod-key');
        $vp->setSandboxAppKey('sandbox-key');

        expect($vp->getAppKey(VirtualPayEnv::PRODUCTION))->toBe('prod-key');
    });

    it('沙箱环境返回沙箱 key', function () {
        $vp = new WechatConfigVirtualPay();
        $vp->setAppKey('prod-key');
        $vp->setSandboxAppKey('sandbox-key');

        expect($vp->getAppKey(VirtualPayEnv::SANDBOX))->toBe('sandbox-key');
    });

    it('环境为 1 且已配置时返回沙箱 key', function () {
        $vp = new WechatConfigVirtualPay();
        $vp->setAppKey('prod-key');
        $vp->setSandboxAppKey('sandbox-key');

        expect($vp->getAppKey(1))->toBe('sandbox-key');
    });

    it('环境为 1 且无沙箱 key 时抛出异常', function () {
        $vp = new WechatConfigVirtualPay();
        $vp->setAppKey('prod-key');

        $vp->getAppKey(1);
    })->throws(InvalidConfigException::class, null, Exception::CONFIG_WECHAT_INVALID);

    it('环境为 1 且均未配置时抛出异常', function () {
        $vp = new WechatConfigVirtualPay();

        $vp->getAppKey(1);
    })->throws(InvalidConfigException::class, null, Exception::CONFIG_WECHAT_INVALID);
});