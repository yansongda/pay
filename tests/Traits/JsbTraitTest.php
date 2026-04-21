<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\JsbTrait;
use Yansongda\Supports\Collection;

class JsbTraitStub
{
    use JsbTrait;
}

class JsbTraitTest extends TestCase
{
    public function testGetJsbUrl(): void
    {
        self::assertEquals('https://yansongda.cn', JsbTraitStub::getJsbUrl(new JsbConfig([
            'partner_id' => '6a13eab71c4f4b0aa4757eda6fc59710',
            'public_key_code' => '00',
            'mch_secret_cert_path' => __DIR__.'/../Cert/EpayKey.pem',
            'jsb_public_cert_path' => __DIR__.'/../Cert/jschina.cer',
        ]), new Collection(['_url' => 'https://yansongda.cn'])));
        self::assertEquals('https://mybank.jsbchina.cn:577/eis/merchant/merchantServices.htm', JsbTraitStub::getJsbUrl(new JsbConfig([
            'partner_id' => '6a13eab71c4f4b0aa4757eda6fc59710',
            'public_key_code' => '00',
            'mch_secret_cert_path' => __DIR__.'/../Cert/EpayKey.pem',
            'jsb_public_cert_path' => __DIR__.'/../Cert/jschina.cer',
            'mode' => Pay::MODE_NORMAL,
        ]), new Collection()));
        self::assertEquals('https://epaytest.jsbchina.cn:9999/eis/merchant/merchantServices.htm', JsbTraitStub::getJsbUrl(new JsbConfig([
            'partner_id' => '6a13eab71c4f4b0aa4757eda6fc59710',
            'public_key_code' => '00',
            'mch_secret_cert_path' => __DIR__.'/../Cert/EpayKey.pem',
            'jsb_public_cert_path' => __DIR__.'/../Cert/jschina.cer',
            'mode' => Pay::MODE_SANDBOX,
        ]), new Collection()));
    }

    public function testVerifyJsbSignEmpty(): void
    {
        $config = $this->getJsbConfig();

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        JsbTraitStub::verifyJsbSign($config, 'content', '');
    }

    public function testVerifyJsbSignMissingConfig(): void
    {
        $config = $this->getJsbConfig();

        $config->setJsbPublicCertPath('');

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_JSB_INVALID);

        JsbTraitStub::verifyJsbSign($config, 'content', 'sign');
    }

    private function getJsbConfig(): JsbConfig
    {
        $config = JsbTraitStub::getProviderConfig('jsb', []);

        self::assertInstanceOf(JsbConfig::class, $config);

        return $config;
    }
}
