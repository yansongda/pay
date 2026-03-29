<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Util;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Util\Certification;

class CertificationTest extends TestCase
{
    public function testSnNormal(): void
    {
        $certPath = __DIR__.'/../Cert/alipayAppPublicCert.crt';
        $sn = Certification::sn($certPath);

        self::assertEquals('e90dd23a37c5c7b616e003970817ff82', $sn);
    }

    public function testSnNonRSAAlgorithm(): void
    {
        $rootCertContent = file_get_contents(__DIR__.'/../Cert/alipayRootCert.crt');

        $certs = explode('-----END CERTIFICATE-----', $rootCertContent);

        foreach ($certs as $cert) {
            if (empty(trim($cert))) {
                continue;
            }

            $ssl = openssl_x509_parse($cert.'-----END CERTIFICATE-----');

            if (false === $ssl) {
                continue;
            }

            if (!in_array($ssl['signatureTypeLN'] ?? '', ['sha1WithRSAEncryption', 'sha256WithRSAEncryption'])) {
                $sn = Certification::sn($cert.'-----END CERTIFICATE-----');
                self::assertEquals('', $sn);

                return;
            }
        }

        self::fail('未找到非 RSA 签名算法的证书');
    }

    public function testSnInvalidCert(): void
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_INVALID);
        self::expectExceptionMessage('配置异常: 解析证书失败');

        Certification::sn('/invalid/path/to/cert.crt');
    }

    public function testSnsWithStringPath(): void
    {
        $certPath = __DIR__.'/../Cert/alipayRootCert.crt';
        $sns = Certification::sns($certPath);

        self::assertCount(2, $sns);
        self::assertEquals('687b59193f3f462dd5336e5abf83c5d8', $sns[0]);
        self::assertEquals('02941eef3187dddf3d3b83462e1dfcf6', $sns[1]);
    }

    public function testSnsWithArray(): void
    {
        $rootCertContent = file_get_contents(__DIR__.'/../Cert/alipayRootCert.crt');
        $certs = explode('-----END CERTIFICATE-----', $rootCertContent);

        $completeCerts = [];
        foreach ($certs as $cert) {
            if (!empty(trim($cert))) {
                $completeCerts[] = $cert.'-----END CERTIFICATE-----';
            }
        }

        $sns = Certification::sns($completeCerts);

        self::assertCount(2, $sns);
        self::assertEquals('687b59193f3f462dd5336e5abf83c5d8', $sns[0]);
        self::assertEquals('02941eef3187dddf3d3b83462e1dfcf6', $sns[1]);
    }

    public function testSnsFilterNonRSA(): void
    {
        $certPath = __DIR__.'/../Cert/alipayRootCert.crt';
        $sns = Certification::sns($certPath);

        foreach ($sns as $sn) {
            self::assertNotEmpty($sn);
            self::assertEquals(32, strlen($sn));
        }
    }

    public function testSnsEmptyResult(): void
    {
        $sns = Certification::sns([]);

        self::assertEmpty($sns);
    }
}
