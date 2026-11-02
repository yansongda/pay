<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;

beforeEach(function () {
    CertManager::clearCache();
});

it('get public cert from file', function () {
    $path = __DIR__.'/Cert/alipayPublicCert.crt';

    expect(CertManager::getPublicCert($path))->toBe(file_get_contents($path));
});

it('get public cert from string', function () {
    $content = file_get_contents(__DIR__.'/Cert/alipayPublicCert.crt');

    expect(CertManager::getPublicCert($content))->toBe($content);
});

it('get public cert cache hit', function () {
    $source = __DIR__.'/Cert/alipayPublicCert.crt';
    $path = sys_get_temp_dir().'/'.uniqid('cert-manager-public-', true).'.crt';

    copy($source, $path);

    try {
        $first = CertManager::getPublicCert($path);
        file_put_contents($path, 'changed-cert-content');

        expect(CertManager::getPublicCert($path))->toBe($first);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

it('get public cert info from file', function () {
    $path = __DIR__.'/Cert/alipayAppPublicCert.crt';
    $expected = openssl_x509_parse(file_get_contents($path));

    expect($expected)->toBeArray();
    expect(CertManager::getPublicCertInfo($path))->toBe($expected);
});

it('get public cert info from string', function () {
    $content = file_get_contents(__DIR__.'/Cert/alipayAppPublicCert.crt');
    $expected = openssl_x509_parse($content);

    expect($expected)->toBeArray();
    expect(CertManager::getPublicCertInfo($content))->toBe($expected);
});

it('get public cert info cache hit', function () {
    $source = __DIR__.'/Cert/alipayAppPublicCert.crt';
    $changed = file_get_contents(__DIR__.'/Cert/wechatPublicKey.crt');
    $path = sys_get_temp_dir().'/'.uniqid('cert-manager-public-info-', true).'.crt';

    copy($source, $path);

    try {
        $first = CertManager::getPublicCertInfo($path);
        file_put_contents($path, $changed);

        expect(CertManager::getPublicCertInfo($path))->toBe($first);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

it('get public cert info after clear cache', function () {
    $source = __DIR__.'/Cert/alipayAppPublicCert.crt';
    $changed = file_get_contents(__DIR__.'/Cert/wechatPublicKey.crt');
    $path = sys_get_temp_dir().'/'.uniqid('cert-manager-public-info-clear-', true).'.crt';

    copy($source, $path);

    try {
        $first = CertManager::getPublicCertInfo($path);
        file_put_contents($path, $changed);

        CertManager::clearCache();

        $second = CertManager::getPublicCertInfo($path);

        expect($second)->not->toBe($first);
        expect($second)->toBe(openssl_x509_parse($changed));
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

it('get public cert info throws exception when cert invalid', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 解析证书失败');

    CertManager::getPublicCertInfo('invalid-cert-content');
});

it('get alipay app cert sn from file', function () {
    $path = __DIR__.'/Cert/alipayAppPublicCert.crt';

    expect(CertManager::alipayGetAppCertSn($path))->toBe('e90dd23a37c5c7b616e003970817ff82');
});

it('get alipay app cert sn from string', function () {
    $content = file_get_contents(__DIR__.'/Cert/alipayAppPublicCert.crt');

    expect(CertManager::alipayGetAppCertSn($content))->toBe('e90dd23a37c5c7b616e003970817ff82');
});

it('get alipay app cert sn cache hit', function () {
    $source = __DIR__.'/Cert/alipayAppPublicCert.crt';
    $path = sys_get_temp_dir().'/'.uniqid('cert-manager-alipay-app-sn-', true).'.crt';

    copy($source, $path);

    try {
        $first = CertManager::alipayGetAppCertSn($path);
        file_put_contents($path, 'changed-cert-content');

        expect(CertManager::alipayGetAppCertSn($path))->toBe($first);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

it('multi tenant alipay cert sn isolation', function () {
    $path1 = __DIR__.'/Cert/alipayAppPublicCert.crt';
    $path2 = __DIR__.'/Cert/alipayPublicCert.crt';

    $sn1 = CertManager::alipayGetAppCertSn($path1);
    $sn2 = CertManager::alipayGetAppCertSn($path2);

    expect($sn2)->not->toBe($sn1);
    expect(CertManager::alipayGetAppCertSn($path1))->toBe($sn1);
    expect(CertManager::alipayGetAppCertSn($path2))->toBe($sn2);
});

it('get alipay root cert sn from file', function () {
    $path = __DIR__.'/Cert/alipayRootCert.crt';

    expect(CertManager::alipayGetRootCertSn($path))->toBe('687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6');
});

it('get alipay root cert sn from string', function () {
    $content = file_get_contents(__DIR__.'/Cert/alipayRootCert.crt');

    expect(CertManager::alipayGetRootCertSn($content))->toBe('687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6');
});

it('get alipay root cert sn cache hit', function () {
    $source = __DIR__.'/Cert/alipayRootCert.crt';
    $path = sys_get_temp_dir().'/'.uniqid('cert-manager-alipay-root-sn-', true).'.crt';

    copy($source, $path);

    try {
        $first = CertManager::alipayGetRootCertSn($path);
        file_put_contents($path, 'changed-cert-content');

        expect(CertManager::alipayGetRootCertSn($path))->toBe($first);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

it('get private cert from file', function () {
    $path = __DIR__.'/Cert/wechatAppPrivateKey.pem';

    expect(CertManager::getPrivateCert($path))->toBe(file_get_contents($path));
});

it('get private cert from string', function () {
    $key = file_get_contents(__DIR__.'/Cert/wechatAppPrivateKey.pem');
    $key = preg_replace('/-----BEGIN PRIVATE KEY-----|-----END PRIVATE KEY-----|\s+/', '', $key);

    $expected = "-----BEGIN RSA PRIVATE KEY-----\n"
        .wordwrap($key, 64, "\n", true)
        ."\n-----END RSA PRIVATE KEY-----";

    expect(CertManager::getPrivateCert($key))->toBe($expected);
});

it('clear cache', function () {
    $source = __DIR__.'/Cert/alipayPublicCert.crt';
    $path = sys_get_temp_dir().'/'.uniqid('cert-manager-clear-', true).'.crt';

    copy($source, $path);

    try {
        $first = CertManager::getPublicCert($path);
        file_put_contents($path, 'changed-cert-content');

        CertManager::clearCache();

        expect(CertManager::getPublicCert($path))->not->toBe($first);
        expect(CertManager::getPublicCert($path))->toBe('changed-cert-content');
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

it('clear cache clears wechat certs', function () {
    CertManager::wechatSetCertBySerial('default', 'serial-1', 'cert-content');

    expect(CertManager::wechatGetCertBySerial('default', 'serial-1'))->not->toBeNull();

    CertManager::clearCache();

    expect(CertManager::wechatGetCertBySerial('default', 'serial-1'))->toBeNull();
});

it('get pkcs12 certs from file', function () {
    $path = __DIR__.'/Cert/unipayAppCert.pfx';
    $certs = CertManager::unipayGetPkcs12Certs($path, '000000');

    expect($certs)->toHaveKey('cert');
    expect($certs)->toHaveKey('pkey');
});

it('get pkcs12 certs cache hit', function () {
    $path = __DIR__.'/Cert/unipayAppCert.pfx';

    $first = CertManager::unipayGetPkcs12Certs($path, '000000');
    $second = CertManager::unipayGetPkcs12Certs($path, '000000');

    expect($second)->toBe($first);
});

it('clear cache invalidates alipay and unipay caches', function () {
    $path = __DIR__.'/Cert/alipayAppPublicCert.crt';

    $sn1 = CertManager::alipayGetAppCertSn($path);
    CertManager::clearCache();
    $sn2 = CertManager::alipayGetAppCertSn($path);

    expect($sn2)->toBe($sn1);

    $rootPath = __DIR__.'/Cert/alipayRootCert.crt';
    $rootSn1 = CertManager::alipayGetRootCertSn($rootPath);
    CertManager::clearCache();
    $rootSn2 = CertManager::alipayGetRootCertSn($rootPath);

    expect($rootSn2)->toBe($rootSn1);

    $pfxPath = __DIR__.'/Cert/unipayAppCert.pfx';
    $password = '000000';

    if (is_file($pfxPath)) {
        $certs1 = CertManager::unipayGetPkcs12Certs($pfxPath, $password);
        $certId1 = CertManager::unipayGetCertId($pfxPath, $password);

        CertManager::clearCache();

        $certs2 = CertManager::unipayGetPkcs12Certs($pfxPath, $password);
        $certId2 = CertManager::unipayGetCertId($pfxPath, $password);

        expect($certs2)->toBe($certs1);
        expect($certId2)->toBe($certId1);
    }
});

it('get pkcs12 certs wrong password throws exception', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 读取证书失败，确认参数是否正确');

    CertManager::unipayGetPkcs12Certs(__DIR__.'/Cert/unipayAppCert.pfx', 'wrong_password');
});

it('get unipay cert id from file', function () {
    $path = __DIR__.'/Cert/unipayAppCert.pfx';

    expect(CertManager::unipayGetCertId($path, '000000'))->toBe('69903319369');
});

it('get unipay cert id cache hit', function () {
    $path = __DIR__.'/Cert/unipayAppCert.pfx';

    $first = CertManager::unipayGetCertId($path, '000000');
    $second = CertManager::unipayGetCertId($path, '000000');

    expect($second)->toBe($first);
});

it('get unipay cert id invalid cert throws exception', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 读取证书失败，确认参数是否正确');

    CertManager::unipayGetCertId('not-a-real-pkcs12-content', '000000');
});

