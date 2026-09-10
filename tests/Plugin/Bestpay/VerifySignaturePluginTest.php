<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Bestpay\V1\VerifySignaturePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\BestpayTrait;
use Yansongda\Supports\Collection;

class VerifySignaturePluginTest extends TestCase
{
    use BestpayTrait;

    public function testValidResponseSign(): void
    {
        $config = $this->makeConfig();
        $body = [
            'success' => true,
            'errorCode' => null,
            'errorMsg' => null,
            'result' => ['outTradeNo' => 'ORDER001', 'tradeStatus' => 'SUCCESS'],
        ];
        $body['sign'] = self::signBestpayContent($config, self::getBestpayResponseSignContent($body));

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'default']);
        $rocket->setDirection(\Yansongda\Artful\Direction\CollectionDirection::class);
        $rocket->setDestination(new Collection($body));

        $result = (new VerifySignaturePlugin())->assembly($rocket, fn ($r) => $r);

        self::assertTrue((bool) $result->getDestination()->get('success'));
    }

    public function testInvalidResponseSign(): void
    {
        $this->expectException(InvalidSignException::class);

        $body = [
            'success' => true,
            'result' => ['outTradeNo' => 'ORDER001'],
            'sign' => base64_encode('not-a-real-sign'),
        ];

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'default']);
        $rocket->setDirection(\Yansongda\Artful\Direction\CollectionDirection::class);
        $rocket->setDestination(new Collection($body));

        (new VerifySignaturePlugin())->assembly($rocket, fn ($r) => $r);
    }

    public function testResponseSignContentNestedJson(): void
    {
        $content = self::getBestpayResponseSignContent([
            'result' => ['b' => 1, 'a' => 2],
            'success' => true,
            'errorCode' => null,
            'sign' => 'x',
        ]);

        self::assertEquals(
            'errorCode=null&result={"a":2,"b":1}&success=true',
            $content
        );
    }

    private function makeConfig(): BestpayConfig
    {
        return new BestpayConfig([
            'merchant_no' => '3178033925245778',
            'institution_code' => '3178033925245778',
            'mch_secret_cert_path' => __DIR__.'/../../Cert/bestpay/bestpay.p12',
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => __DIR__.'/../../Cert/bestpay/bestpay.cer',
        ]);
    }
}
