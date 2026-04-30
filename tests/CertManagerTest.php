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

        Assert::assertSame('e90dd23a37c5c7b616e003970817ff82', CertManager::alipayGetAppCertSn($path));
    }

    public function testGetAlipayAppCertSnFromString(): void
    {
        $content = file_get_contents(__DIR__.'/Cert/alipayAppPublicCert.crt');

        Assert::assertSame('e90dd23a37c5c7b616e003970817ff82', CertManager::alipayGetAppCertSn($content));
    }

    public function testGetAlipayAppCertSnCacheHit(): void
    {
        $source = __DIR__.'/Cert/alipayAppPublicCert.crt';
        $path = sys_get_temp_dir().'/'.uniqid('cert-manager-alipay-app-sn-', true).'.crt';

        copy($source, $path);

        try {
            $first = CertManager::alipayGetAppCertSn($path);
            file_put_contents($path, 'changed-cert-content');

            Assert::assertSame($first, CertManager::alipayGetAppCertSn($path));
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testMultiTenantAlipayCertSnIsolation(): void
    {
        $path1 = __DIR__.'/Cert/alipayAppPublicCert.crt';
        $path2 = __DIR__.'/Cert/alipayPublicCert.crt';

        $sn1 = CertManager::alipayGetAppCertSn($path1);
        $sn2 = CertManager::alipayGetAppCertSn($path2);

        Assert::assertNotSame($sn1, $sn2);
        Assert::assertSame($sn1, CertManager::alipayGetAppCertSn($path1));
        Assert::assertSame($sn2, CertManager::alipayGetAppCertSn($path2));
    }

    public function testGetAlipayRootCertSnFromFile(): void
    {
        $path = __DIR__.'/Cert/alipayRootCert.crt';

        Assert::assertSame('687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6', CertManager::alipayGetRootCertSn($path));
    }

    public function testGetAlipayRootCertSnFromString(): void
    {
        $content = file_get_contents(__DIR__.'/Cert/alipayRootCert.crt');

        Assert::assertSame('687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6', CertManager::alipayGetRootCertSn($content));
    }

    public function testGetAlipayRootCertSnCacheHit(): void
    {
        $source = __DIR__.'/Cert/alipayRootCert.crt';
        $path = sys_get_temp_dir().'/'.uniqid('cert-manager-alipay-root-sn-', true).'.crt';

        copy($source, $path);

        try {
            $first = CertManager::alipayGetRootCertSn($path);
            file_put_contents($path, 'changed-cert-content');

            Assert::assertSame($first, CertManager::alipayGetRootCertSn($path));
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

    public function testClearCacheClearsWechatCerts(): void
    {
        CertManager::wechatSetCertBySerial('default', 'serial-1', 'cert-content');

        Assert::assertNotNull(CertManager::wechatGetCertBySerial('default', 'serial-1'));

        CertManager::clearCache();

        Assert::assertNull(CertManager::wechatGetCertBySerial('default', 'serial-1'));
    }

    public function testGetPkcs12CertsFromFile(): void
    {
        $path = __DIR__.'/Cert/unipayAppCert.pfx';
        $certs = CertManager::unipayGetPkcs12Certs($path, '000000');

        Assert::assertArrayHasKey('cert', $certs);
        Assert::assertArrayHasKey('pkey', $certs);
    }

    public function testGetPkcs12CertsCacheHit(): void
    {
        $path = __DIR__.'/Cert/unipayAppCert.pfx';

        $first = CertManager::unipayGetPkcs12Certs($path, '000000');
        $second = CertManager::unipayGetPkcs12Certs($path, '000000');

        Assert::assertSame($first, $second);
    }

    public function testClearCacheInvalidatesAlipayAndUnipayCaches(): void
    {
        $path = __DIR__.'/Cert/alipayAppPublicCert.crt';

        $sn1 = CertManager::alipayGetAppCertSn($path);
        CertManager::clearCache();
        $sn2 = CertManager::alipayGetAppCertSn($path);

        Assert::assertSame($sn1, $sn2);

        $rootPath = __DIR__.'/Cert/alipayRootCert.crt';
        $rootSn1 = CertManager::alipayGetRootCertSn($rootPath);
        CertManager::clearCache();
        $rootSn2 = CertManager::alipayGetRootCertSn($rootPath);

        Assert::assertSame($rootSn1, $rootSn2);

        $pfxPath = __DIR__.'/Cert/unipayAppCert.pfx';
        $password = '000000';

        if (is_file($pfxPath)) {
            $certs1 = CertManager::unipayGetPkcs12Certs($pfxPath, $password);
            $certId1 = CertManager::unipayGetCertId($pfxPath, $password);

            CertManager::clearCache();

            $certs2 = CertManager::unipayGetPkcs12Certs($pfxPath, $password);
            $certId2 = CertManager::unipayGetCertId($pfxPath, $password);

            Assert::assertSame($certs1, $certs2);
            Assert::assertSame($certId1, $certId2);
        }
    }

    public function testGetPkcs12CertsWrongPasswordThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 读取证书失败，确认参数是否正确');

        CertManager::unipayGetPkcs12Certs(__DIR__.'/Cert/unipayAppCert.pfx', 'wrong_password');
    }

    public function testGetUnipayCertIdFromFile(): void
    {
        $path = __DIR__.'/Cert/unipayAppCert.pfx';

        Assert::assertSame('69903319369', CertManager::unipayGetCertId($path, '000000'));
    }

    public function testGetUnipayCertIdCacheHit(): void
    {
        $path = __DIR__.'/Cert/unipayAppCert.pfx';

        $first = CertManager::unipayGetCertId($path, '000000');
        $second = CertManager::unipayGetCertId($path, '000000');

        Assert::assertSame($first, $second);
    }

    public function testGetUnipayCertIdInvalidCertThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 读取证书失败，确认参数是否正确');

        CertManager::unipayGetCertId('not-a-real-pkcs12-content', '000000');
    }
}
