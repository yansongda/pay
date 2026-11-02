<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;

beforeEach(function () {
    CertManager::clearCache();
});

it('从文件获取公钥证书', function () {
    $path = cert_path('alipayPublicCert.crt');

    expect(CertManager::getPublicCert($path))->toBe(file_get_contents($path));
});

it('从字符串获取公钥证书', function () {
    $content = file_get_contents(cert_path('alipayPublicCert.crt'));

    expect(CertManager::getPublicCert($content))->toBe($content);
});

it('公钥证书缓存命中', function () {
    $source = cert_path('alipayPublicCert.crt');
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

it('从文件获取公钥证书信息', function () {
    $path = cert_path('alipayAppPublicCert.crt');
    $expected = openssl_x509_parse(file_get_contents($path));

    expect($expected)->toBeArray();
    expect(CertManager::getPublicCertInfo($path))->toBe($expected);
});

it('从字符串获取公钥证书信息', function () {
    $content = file_get_contents(cert_path('alipayAppPublicCert.crt'));
    $expected = openssl_x509_parse($content);

    expect($expected)->toBeArray();
    expect(CertManager::getPublicCertInfo($content))->toBe($expected);
});

it('公钥证书信息缓存命中', function () {
    $source = cert_path('alipayAppPublicCert.crt');
    $changed = file_get_contents(cert_path('wechatPublicKey.crt'));
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

it('清空缓存后获取公钥证书信息', function () {
    $source = cert_path('alipayAppPublicCert.crt');
    $changed = file_get_contents(cert_path('wechatPublicKey.crt'));
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

it('证书非法时获取公钥证书信息抛出异常', function () {

    CertManager::getPublicCertInfo('invalid-cert-content');
})->throws(InvalidConfigException::class, '配置异常: 解析证书失败');

it('从文件获取支付宝应用证书序列号', function () {
    $path = cert_path('alipayAppPublicCert.crt');

    expect(CertManager::alipayGetAppCertSn($path))->toBe('e90dd23a37c5c7b616e003970817ff82');
});

it('从字符串获取支付宝应用证书序列号', function () {
    $content = file_get_contents(cert_path('alipayAppPublicCert.crt'));

    expect(CertManager::alipayGetAppCertSn($content))->toBe('e90dd23a37c5c7b616e003970817ff82');
});

it('支付宝应用证书序列号缓存命中', function () {
    $source = cert_path('alipayAppPublicCert.crt');
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

it('多租户支付宝证书序列号隔离', function () {
    $path1 = cert_path('alipayAppPublicCert.crt');
    $path2 = cert_path('alipayPublicCert.crt');

    $sn1 = CertManager::alipayGetAppCertSn($path1);
    $sn2 = CertManager::alipayGetAppCertSn($path2);

    expect($sn2)->not->toBe($sn1);
    expect(CertManager::alipayGetAppCertSn($path1))->toBe($sn1);
    expect(CertManager::alipayGetAppCertSn($path2))->toBe($sn2);
});

it('从文件获取支付宝根证书序列号', function () {
    $path = cert_path('alipayRootCert.crt');

    expect(CertManager::alipayGetRootCertSn($path))->toBe('687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6');
});

it('从字符串获取支付宝根证书序列号', function () {
    $content = file_get_contents(cert_path('alipayRootCert.crt'));

    expect(CertManager::alipayGetRootCertSn($content))->toBe('687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6');
});

it('支付宝根证书序列号缓存命中', function () {
    $source = cert_path('alipayRootCert.crt');
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

it('从文件获取私钥证书', function () {
    $path = cert_path('wechatAppPrivateKey.pem');

    expect(CertManager::getPrivateCert($path))->toBe(file_get_contents($path));
});

it('从字符串获取私钥证书', function () {
    $key = file_get_contents(cert_path('wechatAppPrivateKey.pem'));
    $key = preg_replace('/-----BEGIN PRIVATE KEY-----|-----END PRIVATE KEY-----|\s+/', '', $key);

    $expected = "-----BEGIN RSA PRIVATE KEY-----\n"
        .wordwrap($key, 64, "\n", true)
        ."\n-----END RSA PRIVATE KEY-----";

    expect(CertManager::getPrivateCert($key))->toBe($expected);
});

it('清空缓存', function () {
    $source = cert_path('alipayPublicCert.crt');
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

it('清空缓存清除微信证书', function () {
    CertManager::wechatSetCertBySerial('default', 'serial-1', 'cert-content');

    expect(CertManager::wechatGetCertBySerial('default', 'serial-1'))->not->toBeNull();

    CertManager::clearCache();

    expect(CertManager::wechatGetCertBySerial('default', 'serial-1'))->toBeNull();
});

it('从文件获取 PKCS12 证书', function () {
    $path = cert_path('unipayAppCert.pfx');
    $certs = CertManager::unipayGetPkcs12Certs($path, '000000');

    expect($certs)->toHaveKey('cert');
    expect($certs)->toHaveKey('pkey');
});

it('PKCS12 证书缓存命中', function () {
    $path = cert_path('unipayAppCert.pfx');

    $first = CertManager::unipayGetPkcs12Certs($path, '000000');
    $second = CertManager::unipayGetPkcs12Certs($path, '000000');

    expect($second)->toBe($first);
});

it('清空缓存使支付宝与银联缓存失效', function () {
    $path = cert_path('alipayAppPublicCert.crt');

    $sn1 = CertManager::alipayGetAppCertSn($path);
    CertManager::clearCache();
    $sn2 = CertManager::alipayGetAppCertSn($path);

    expect($sn2)->toBe($sn1);

    $rootPath = cert_path('alipayRootCert.crt');
    $rootSn1 = CertManager::alipayGetRootCertSn($rootPath);
    CertManager::clearCache();
    $rootSn2 = CertManager::alipayGetRootCertSn($rootPath);

    expect($rootSn2)->toBe($rootSn1);

    $pfxPath = cert_path('unipayAppCert.pfx');
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

it('PKCS12 证书密码错误抛出异常', function () {

    CertManager::unipayGetPkcs12Certs(cert_path('unipayAppCert.pfx'), 'wrong_password');
})->throws(InvalidConfigException::class, '配置异常: 读取证书失败，确认参数是否正确');

it('从文件获取银联证书 ID', function () {
    $path = cert_path('unipayAppCert.pfx');

    expect(CertManager::unipayGetCertId($path, '000000'))->toBe('69903319369');
});

it('银联证书 ID 缓存命中', function () {
    $path = cert_path('unipayAppCert.pfx');

    $first = CertManager::unipayGetCertId($path, '000000');
    $second = CertManager::unipayGetCertId($path, '000000');

    expect($second)->toBe($first);
});

it('银联证书 ID：非法证书抛出异常', function () {

    CertManager::unipayGetCertId('not-a-real-pkcs12-content', '000000');
})->throws(InvalidConfigException::class, '配置异常: 读取证书失败，确认参数是否正确');

