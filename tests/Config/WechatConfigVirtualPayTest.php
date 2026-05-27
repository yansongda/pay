<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use PHPUnit\Framework\Attributes\CoversNothing;
use Yansongda\Pay\Config\WechatConfigVirtualPay;
use Yansongda\Pay\Tests\TestCase;

#[CoversNothing]
class WechatConfigVirtualPayTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $vp = new WechatConfigVirtualPay();

        self::assertNull($vp->getAppKey());
        self::assertNull($vp->getSandboxAppKey());
        self::assertNull($vp->getOfferId());
    }

    public function testSetAndGetAppKey(): void
    {
        $vp = new WechatConfigVirtualPay();
        $vp->setAppKey('prod-key');

        self::assertSame('prod-key', $vp->getAppKey());
    }

    public function testSetAndGetSandboxAppKey(): void
    {
        $vp = new WechatConfigVirtualPay();
        $vp->setSandboxAppKey('sandbox-key');

        self::assertSame('sandbox-key', $vp->getSandboxAppKey());
    }

    public function testSetAndGetOfferId(): void
    {
        $vp = new WechatConfigVirtualPay();
        $vp->setOfferId('offer-123');

        self::assertSame('offer-123', $vp->getOfferId());
    }

    public function testGetAppKeyWithoutEnvReturnsAppKey(): void
    {
        $vp = new WechatConfigVirtualPay();
        $vp->setAppKey('prod-key');

        self::assertSame('prod-key', $vp->getAppKey(0));
    }

    public function testGetAppKeyWithEnvOneReturnsSandboxWhenConfigured(): void
    {
        $vp = new WechatConfigVirtualPay();
        $vp->setAppKey('prod-key');
        $vp->setSandboxAppKey('sandbox-key');

        self::assertSame('sandbox-key', $vp->getAppKey(1));
    }

    public function testGetAppKeyWithEnvOneFallsBackToAppKeyWhenNoSandbox(): void
    {
        $vp = new WechatConfigVirtualPay();
        $vp->setAppKey('prod-key');

        self::assertSame('prod-key', $vp->getAppKey(1));
    }

    public function testGetAppKeyWithEnvOneReturnsNullWhenNeitherConfigured(): void
    {
        $vp = new WechatConfigVirtualPay();

        self::assertNull($vp->getAppKey(1));
    }
}
