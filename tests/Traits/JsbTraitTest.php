<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\JsbTrait;
use Yansongda\Supports\Collection;

class JsbTraitStub
{
use JsbTrait;
}

uses(JsbTraitTestHelpers::class);

it('获取江苏银行 URL', function () {
    expect(JsbTraitStub::getJsbUrl(new JsbConfig([
        'partner_id' => '6a13eab71c4f4b0aa4757eda6fc59710',
        'public_key_code' => '00',
        'mch_secret_cert_path' => __DIR__.'/../Cert/EpayKey.pem',
        'jsb_public_cert_path' => __DIR__.'/../Cert/jschina.cer',
    ]), new Collection(['_url' => 'https://yansongda.cn'])))->toEqual('https://yansongda.cn');
    expect(JsbTraitStub::getJsbUrl(new JsbConfig([
        'partner_id' => '6a13eab71c4f4b0aa4757eda6fc59710',
        'public_key_code' => '00',
        'mch_secret_cert_path' => __DIR__.'/../Cert/EpayKey.pem',
        'jsb_public_cert_path' => __DIR__.'/../Cert/jschina.cer',
        'mode' => PayMode::NORMAL,
    ]), new Collection()))->toEqual('https://mybank.jsbchina.cn:577/eis/merchant/merchantServices.htm');
    expect(JsbTraitStub::getJsbUrl(new JsbConfig([
        'partner_id' => '6a13eab71c4f4b0aa4757eda6fc59710',
        'public_key_code' => '00',
        'mch_secret_cert_path' => __DIR__.'/../Cert/EpayKey.pem',
        'jsb_public_cert_path' => __DIR__.'/../Cert/jschina.cer',
        'mode' => PayMode::SANDBOX,
    ]), new Collection()))->toEqual('https://epaytest.jsbchina.cn:9999/eis/merchant/merchantServices.htm');
});

it('江苏银行验签为空', function () {
    $config = $this->getJsbConfig();

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);

    JsbTraitStub::verifyJsbSign($config, 'content', '');
});

it('江苏银行验签缺少配置', function () {
    $config = $this->getJsbConfig();

    $config->setJsbPublicCertPath('');

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_JSB_INVALID);

    JsbTraitStub::verifyJsbSign($config, 'content', 'sign');
});

trait JsbTraitTestHelpers
{
    private function getJsbConfig(): JsbConfig
    {
        $config = JsbTraitStub::getProviderConfig('jsb', []);
    
        self::assertInstanceOf(JsbConfig::class, $config);
    
        return $config;
    }
}
