<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests;

use PHPUnit\Framework\Assert;
use Yansongda\Artful\Exception\InvalidConfigException;
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

    public function testGetPublicCertInfoFromFile(): void
    {
        $path = __DIR__.'/Cert/alipayAppPublicCert.crt';
        $expected = openssl_x509_parse(file_get_contents($path));

        Assert::assertIsArray($expected);
        Assert::assertSame($expected, CertManager::getPublicCertInfo($path));
    }

    public function testGetPublicCertInfoFromString(): void
    {
        $content = file_get_contents(__DIR__.'/Cert/alipayAppPublicCert.crt');
        $expected = openssl_x509_parse($content);

        Assert::assertIsArray($expected);
        Assert::assertSame($expected, CertManager::getPublicCertInfo($content));
    }

    public function testGetPublicCertInfoCacheHit(): void
    {
        $source = __DIR__.'/Cert/alipayAppPublicCert.crt';
        $changed = file_get_contents(__DIR__.'/Cert/wechatPublicKey.crt');
        $path = sys_get_temp_dir().'/'.uniqid('cert-manager-public-info-', true).'.crt';

        copy($source, $path);

        try {
            $first = CertManager::getPublicCertInfo($path);
            file_put_contents($path, $changed);

            Assert::assertSame($first, CertManager::getPublicCertInfo($path));
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testGetPublicCertInfoAfterClearCache(): void
    {
        $source = __DIR__.'/Cert/alipayAppPublicCert.crt';
        $changed = file_get_contents(__DIR__.'/Cert/wechatPublicKey.crt');
        $path = sys_get_temp_dir().'/'.uniqid('cert-manager-public-info-clear-', true).'.crt';

        copy($source, $path);

        try {
            $first = CertManager::getPublicCertInfo($path);
            file_put_contents($path, $changed);

            CertManager::clearCache();

            $second = CertManager::getPublicCertInfo($path);

            Assert::assertNotSame($first, $second);
            Assert::assertSame(openssl_x509_parse($changed), $second);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testGetPublicCertInfoThrowsExceptionWhenCertInvalid(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 解析证书失败');

        CertManager::getPublicCertInfo('invalid-cert-content');
    }

    public function testGetAlipayAppCertSnFromFile(): void
    {
        $path = __DIR__.'/Cert/alipayAppPublicCert.crt';

        Assert::assertSame('e90dd23a37c5c7b616e003970817ff82', CertManager::getAlipayAppCertSn($path));
    }

    public function testGetAlipayAppCertSnFromString(): void
    {
        $content = file_get_contents(__DIR__.'/Cert/alipayAppPublicCert.crt');

        Assert::assertSame('e90dd23a37c5c7b616e003970817ff82', CertManager::getAlipayAppCertSn($content));
    }

    public function testGetAlipayAppCertSnCacheHit(): void
    {
        $source = __DIR__.'/Cert/alipayAppPublicCert.crt';
        $path = sys_get_temp_dir().'/'.uniqid('cert-manager-alipay-app-sn-', true).'.crt';

        copy($source, $path);

        try {
            $first = CertManager::getAlipayAppCertSn($path);
            file_put_contents($path, 'changed-cert-content');

            Assert::assertSame($first, CertManager::getAlipayAppCertSn($path));
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testGetAlipayRootCertSnFromFile(): void
    {
        $path = __DIR__.'/Cert/alipayRootCert.crt';

        Assert::assertSame('687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6', CertManager::getAlipayRootCertSn($path));
    }

    public function testGetAlipayRootCertSnFromString(): void
    {
        $content = file_get_contents(__DIR__.'/Cert/alipayRootCert.crt');

        Assert::assertSame('687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6', CertManager::getAlipayRootCertSn($content));
    }

    public function testGetAlipayRootCertSnCacheHit(): void
    {
        $source = __DIR__.'/Cert/alipayRootCert.crt';
        $path = sys_get_temp_dir().'/'.uniqid('cert-manager-alipay-root-sn-', true).'.crt';

        copy($source, $path);

        try {
            $first = CertManager::getAlipayRootCertSn($path);
            file_put_contents($path, 'changed-cert-content');

            Assert::assertSame($first, CertManager::getAlipayRootCertSn($path));
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
}
