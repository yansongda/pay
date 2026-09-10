<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Bestpay\V1\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\BestpayTrait;
use Yansongda\Supports\Collection;

class CallbackPluginTest extends TestCase
{
    use BestpayTrait;

    public function testValidCallback(): void
    {
        $config = new \Yansongda\Pay\Config\BestpayConfig([
            'merchant_no' => '3178033925245778',
            'institution_code' => '3178033925245778',
            'mch_secret_cert_path' => __DIR__.'/../../Cert/bestpay/bestpay.p12',
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => __DIR__.'/../../Cert/bestpay/bestpay.cer',
        ]);

        $data = [
            'institutionCode' => '3178033925245778',
            'merchantNo' => '3178033925245778',
            'outTradeNo' => 'ORDER001',
            'notifyType' => 'PAY',
            'totalAmt' => '99',
            'tradeStatus' => 'SUCCESS',
            'tradeNo' => 'TRADE001',
        ];

        $data['sign'] = self::signBestpayContent($config, self::getBestpaySignContent($data));

        $rocket = new Rocket();
        $rocket->setParams(['request' => new Collection($data), 'params' => []]);

        $result = (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);

        self::assertEquals('SUCCESS', $result->getDestination()->get('tradeStatus'));
        self::assertEquals('ORDER001', $result->getDestination()->get('outTradeNo'));
    }

    public function testInvalidSign(): void
    {
        $this->expectException(\Yansongda\Pay\Exception\InvalidSignException::class);

        $data = [
            'outTradeNo' => 'ORDER001',
            'tradeStatus' => 'SUCCESS',
            'sign' => base64_encode('invalid-sign'),
        ];

        $rocket = new Rocket();
        $rocket->setParams(['request' => new Collection($data), 'params' => []]);

        (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);
    }

    public function testMissingRequest(): void
    {
        $this->expectException(\Yansongda\Artful\Exception\InvalidParamsException::class);

        $rocket = new Rocket();
        $rocket->setParams([]);

        (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);
    }
}
