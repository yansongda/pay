<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Bestpay\V1\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\BestpayTrait;
use Yansongda\Supports\Collection;

class CallbackPluginTest extends TestCase
{
    use BestpayTrait;

    public function testValidCallback(): void
    {
        $data = [
            'institutionCode' => '3178033925245778',
            'merchantNo' => '3178033925245778',
            'outTradeNo' => 'ORDER001',
            'notifyType' => 'PAY',
            'totalAmt' => '99',
            'tradeStatus' => 'SUCCESS',
            'tradeNo' => 'TRADE001',
        ];

        $data['sign'] = self::signBestpayContent($this->defaultConfig(), self::getBestpaySignContent($data));

        $rocket = new Rocket();
        $rocket->setParams(['_request' => new Collection($data), '_params' => []]);

        $result = (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);

        self::assertEquals('SUCCESS', $result->getDestination()->get('tradeStatus'));
        self::assertEquals('ORDER001', $result->getDestination()->get('outTradeNo'));
    }

    public function testValidCallbackWithEmptyFields(): void
    {
        // 回调报文含 null / 空串字段时，拼串为 k=null / k=（对齐官方 AssembleSignatureData）
        $data = [
            'institutionCode' => '3178033925245778',
            'merchantNo' => '3178033925245778',
            'outTradeNo' => 'ORDER001',
            'notifyType' => 'REFUND',
            'outRefundNo' => 'REFUND001',
            'refundAmt' => '99',
            'remark' => '',
            'payFinishedDate' => null,
            'tradeStatus' => 'SUCCESS',
        ];

        $data['sign'] = self::signBestpayContent($this->defaultConfig(), self::getBestpaySignContent($data));

        $rocket = new Rocket();
        $rocket->setParams(['_request' => new Collection($data), '_params' => []]);

        $result = (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);

        self::assertEquals('SUCCESS', $result->getDestination()->get('tradeStatus'));
    }

    public function testInvalidSign(): void
    {
        $this->expectException(InvalidSignException::class);

        $data = [
            'outTradeNo' => 'ORDER001',
            'tradeStatus' => 'SUCCESS',
            'sign' => base64_encode('invalid-sign'),
        ];

        $rocket = new Rocket();
        $rocket->setParams(['_request' => new Collection($data), '_params' => []]);

        (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);
    }

    public function testCallbackWithTenantConfig(): void
    {
        $data = [
            'institutionCode' => '3178033925245779',
            'merchantNo' => '3178033925245779',
            'outTradeNo' => 'ORDER002',
            'notifyType' => 'PAY',
            'totalAmt' => '99',
            'tradeStatus' => 'SUCCESS',
        ];

        // 用 second 租户的证书加签
        $data['sign'] = self::signBestpayContent($this->secondConfig(), self::getBestpaySignContent($data));

        $rocket = new Rocket();
        $rocket->setParams(['_request' => new Collection($data), '_params' => ['_config' => 'second']]);

        $result = (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);

        self::assertEquals('ORDER002', $result->getDestination()->get('outTradeNo'));
    }

    public function testCallbackTenantIsolation(): void
    {
        // 用 default 证书加签，但路由到 second 租户（不同平台公钥）→ 验签必须失败
        $this->expectException(InvalidSignException::class);

        $data = [
            'merchantNo' => '3178033925245778',
            'outTradeNo' => 'ORDER003',
            'tradeStatus' => 'SUCCESS',
        ];

        $data['sign'] = self::signBestpayContent($this->defaultConfig(), self::getBestpaySignContent($data));

        $rocket = new Rocket();
        $rocket->setParams(['_request' => new Collection($data), '_params' => ['_config' => 'second']]);

        (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);
    }

    public function testMissingRequest(): void
    {
        $this->expectException(\Yansongda\Artful\Exception\InvalidParamsException::class);

        $rocket = new Rocket();
        $rocket->setParams([]);

        (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);
    }

    private function defaultConfig(): BestpayConfig
    {
        return new BestpayConfig([
            'merchant_no' => '3178033925245778',
            'institution_code' => '3178033925245778',
            'mch_secret_cert_path' => __DIR__.'/../../Cert/bestpay/bestpay.p12',
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => __DIR__.'/../../Cert/bestpay/bestpay.cer',
        ]);
    }

    private function secondConfig(): BestpayConfig
    {
        return new BestpayConfig([
            'merchant_no' => '3178033925245779',
            'institution_code' => '3178033925245779',
            'mch_secret_cert_path' => __DIR__.'/../../Cert/bestpay/second.p12',
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => __DIR__.'/../../Cert/bestpay/second.cer',
        ]);
    }
}
