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
        $path = __DIR__.'/Cert/alipayPublicCert.crt';

        $first = CertManager::getPublicCert($path);
        CertManager::clearCache();

        Assert::assertSame($first, CertManager::getPublicCert($path));
    }
}
