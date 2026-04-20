<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests;

use PHPUnit\Framework\Assert;
use Yansongda\Pay\CertManager;

class CertManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        CertManager::clearCache();
    }

    public function testGetPublicCertFromFile(): void
    {
        $path = __DIR__.'/Cert/alipayPublicCert.crt';

        Assert::assertSame(file_get_contents($path), CertManager::getPublicCert($path));
    }

    public function testGetPublicCertFromString(): void
    {
        $content = file_get_contents(__DIR__.'/Cert/alipayPublicCert.crt');

        Assert::assertSame($content, CertManager::getPublicCert($content));
    }

    public function testGetPublicCertCacheHit(): void
    {
        $source = __DIR__.'/Cert/alipayPublicCert.crt';
        $path = sys_get_temp_dir().'/'.uniqid('cert-manager-public-', true).'.crt';

        copy($source, $path);

        try {
            $first = CertManager::getPublicCert($path);
            file_put_contents($path, 'changed-cert-content');

            Assert::assertSame($first, CertManager::getPublicCert($path));
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testGetPrivateCertFromFile(): void
    {
        $path = __DIR__.'/Cert/wechatAppPrivateKey.pem';

        Assert::assertSame(file_get_contents($path), CertManager::getPrivateCert($path));
    }

    public function testGetPrivateCertFromString(): void
    {
        $key = file_get_contents(__DIR__.'/Cert/wechatAppPrivateKey.pem');
        $key = preg_replace('/-----BEGIN PRIVATE KEY-----|-----END PRIVATE KEY-----|\s+/', '', $key);

        $expected = "-----BEGIN RSA PRIVATE KEY-----\n"
            .wordwrap($key, 64, "\n", true)
            ."\n-----END RSA PRIVATE KEY-----";

        Assert::assertSame($expected, CertManager::getPrivateCert($key));
    }

    public function testClearCache(): void
    {
        $source = __DIR__.'/Cert/alipayPublicCert.crt';
        $path = sys_get_temp_dir().'/'.uniqid('cert-manager-clear-', true).'.crt';

        copy($source, $path);

        try {
            $first = CertManager::getPublicCert($path);
            file_put_contents($path, 'changed-cert-content');

            CertManager::clearCache();

            Assert::assertNotSame($first, CertManager::getPublicCert($path));
            Assert::assertSame('changed-cert-content', CertManager::getPublicCert($path));
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testClearCacheClearsSerialCache(): void
    {
        CertManager::setBySerial('wechat', 'default', 'serial-1', 'cert-content');

        Assert::assertTrue(CertManager::hasBySerial('wechat', 'default', 'serial-1'));

        CertManager::clearCache();

        Assert::assertFalse(CertManager::hasBySerial('wechat', 'default', 'serial-1'));
        Assert::assertNull(CertManager::getBySerial('wechat', 'default', 'serial-1'));
    }

    public function testSetAllBySerialAndGetAllBySerial(): void
    {
        CertManager::setAllBySerial('wechat', 'default', [
            'serial-1' => 'cert-1',
            'serial-2' => 'cert-2',
        ]);

        Assert::assertSame([
            'serial-1' => 'cert-1',
            'serial-2' => 'cert-2',
        ], CertManager::getAllBySerial('wechat', 'default'));
    }

    public function testClearBySerial(): void
    {
        CertManager::setBySerial('wechat', 'default', 'serial-1', 'cert-content');
        CertManager::clearBySerial('wechat', 'default');

        Assert::assertSame([], CertManager::getAllBySerial('wechat', 'default'));
        Assert::assertFalse(CertManager::hasBySerial('wechat', 'default', 'serial-1'));
    }

    public function testClearAllBySerial(): void
    {
        CertManager::setBySerial('wechat', 'default', 'serial-1', 'cert-content');
        CertManager::setBySerial('alipay', 'tenant-a', 'serial-2', 'cert-content-2');

        CertManager::clearAllBySerial();

        Assert::assertSame([], CertManager::getAllBySerial('wechat', 'default'));
        Assert::assertSame([], CertManager::getAllBySerial('alipay', 'tenant-a'));
    }
}
